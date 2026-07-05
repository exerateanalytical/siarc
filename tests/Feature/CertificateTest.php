<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function issueCert($business): string
    {
        $no = certNumberFor($business->id, $business->created_at);
        DB::table('businesses')->where('id', $business->id)->update([
            'certificate_no' => $no,
            'certificate_issued_at' => $business->created_at,
            'certificate_expires_at' => now()->addYear(),
        ]);
        return $no;
    }

    public function test_verification_resolves_a_real_certificate(): void
    {
        $biz = $this->makeBusiness(null, ['name_fr' => 'Poterie Vérif Test', 'status' => 'published']);
        $no = $this->issueCert($biz);

        $this->get('/verification-certificat?numero=' . $no)
            ->assertOk()
            ->assertSee('Poterie Vérif Test')
            ->assertSee($no)
            ->assertSee('Certificat valide');
    }

    public function test_verification_rejects_an_unknown_number(): void
    {
        $this->get('/verification-certificat?numero=GVN-1900-0000000')
            ->assertOk()
            ->assertSee('introuvable')
            ->assertDontSee('Certificat valide');
    }

    public function test_membership_certificate_issues_and_persists_a_number(): void
    {
        $user = $this->makeUser();
        $biz = $this->makeBusiness($user, ['name_fr' => 'Mon Atelier']);
        $this->assertNull(DB::table('businesses')->where('id', $biz->id)->value('certificate_no'));

        $session = ['siac_user' => ['id' => $user->id, 'name' => 'Owner', 'email' => $user->email, 'role' => 'business_owner', 'is_admin' => false]];
        $this->withSession($session)->get('/certificat-adhesion')->assertOk();

        $this->assertNotNull(DB::table('businesses')->where('id', $biz->id)->value('certificate_no'));
    }

    public function test_admin_certificate_registry_renders(): void
    {
        $biz = $this->makeBusiness(null, ['name_fr' => 'Registre Test', 'status' => 'published']);
        $no = $this->issueCert($biz);

        $admin = $this->makeUser();
        $session = ['siac_user' => ['id' => $admin->id, 'name' => 'Admin', 'email' => $admin->email, 'role' => 'super_admin', 'is_admin' => true]];

        $this->withSession($session)->get(route('admin.certificates'))
            ->assertOk()
            ->assertSee('Registre Test')
            ->assertSee($no);
    }
}
