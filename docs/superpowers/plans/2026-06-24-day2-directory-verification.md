# Day 2 Implementation Plan — Company Directory + Verification

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement complete Company Directory CRUD with bilingual search (Meilisearch) and 4-tier government verification workflow.

**Architecture:** Modular — Directory and Verification modules, Scout for search, mock registry drivers for sandbox.

**Tech Stack:** Laravel 13, Laravel Scout, Meilisearch, Spatie Permission, Passport

---

## Conventions for this plan

- All routes are registered under the `api/v1/` prefix by each module's service provider.
- Auth uses Laravel Passport (`auth:api`). Scopes (`companies:write`, `companies:verify`, `admin:compliance`) are enforced with the `scope`/`scopes` middleware. Roles (`govt_reviewer`, etc.) are enforced with Spatie's `role:` middleware on the `api` guard.
- Companies use a **UUID primary key**. The model disables auto-increment and casts the key as a string.
- Bilingual columns come in `_fr` / `_en` pairs (`description_fr`, `description_en`, etc.). Requests validate both; resources expose both plus a `*_localized` convenience based on `app()->getLocale()`.
- Every task is TDD: write the failing test, run it red, implement, run it green, then commit.

Verify the toolchain before starting:

```bash
php artisan --version          # Laravel Framework 13.x
php artisan route:list | grep v1
php -r "echo class_exists('Laravel\\Scout\\Searchable') ? 'scout ok' : 'no scout';"
```

---

## Task D2-1: Directory Module — Company Model + Service

- [ ] Create the four Eloquent models and the `CompanyService`.

### File: `app/Modules/Directory/Models/Company.php`

```php
<?php

namespace App\Modules\Directory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Company extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use Searchable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'trade_name',
        'description_fr',
        'description_en',
        'legal_form',
        'status',
        'verification_status',
        'rccm_number',
        'niu_number',
        'anor_number',
        'cnps_number',
        'cmf_license',
        'incorporation_date',
        'share_capital',
        'city_id',
        'region_id',
        'address',
        'phone',
        'email',
        'website',
        'logo_url',
        'cover_url',
        'is_featured',
        'employee_count_min',
        'employee_count_max',
    ];

    protected $casts = [
        'incorporation_date' => 'date',
        'share_capital'      => 'integer',
        'is_featured'        => 'boolean',
        'view_count'         => 'integer',
        'rating_avg'         => 'decimal:2',
        'rating_count'       => 'integer',
        'employee_count_min' => 'integer',
        'employee_count_max' => 'integer',
    ];

    /**
     * Generate a UUID for the primary key. (HasUuids handles this; method
     * kept explicit so the column list is unambiguous.)
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CompanyMember::class);
    }

    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Directory\Models\Industry::class,
            'company_industry'
        )->withPivot(['sector_id', 'is_primary']);
    }

    /**
     * The index this model is searchable in.
     */
    public function searchableAs(): string
    {
        return 'companies';
    }

    /**
     * The data array indexed by Meilisearch.
     */
    public function toSearchableArray(): array
    {
        return [
            'id'                  => (string) $this->id,
            'name'                => $this->name,
            'trade_name'          => $this->trade_name,
            'slug'                => $this->slug,
            'description_fr'      => $this->description_fr,
            'description_en'      => $this->description_en,
            'legal_form'          => $this->legal_form,
            'status'              => $this->status,
            'verification_status' => $this->verification_status,
            'region_id'           => $this->region_id,
            'city_id'             => $this->city_id,
            'is_featured'         => (bool) $this->is_featured,
            'rating_avg'          => (float) $this->rating_avg,
            'view_count'          => (int) $this->view_count,
            'created_at'          => optional($this->created_at)->timestamp,
        ];
    }

    /**
     * Only index records that are publicly visible.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'active';
    }
}
```

### File: `app/Modules/Directory/Models/CompanyDocument.php`

```php
<?php

namespace App\Modules\Directory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'type',
        'title',
        'file_path',
        'file_hash',
        'file_size',
        'mime_type',
        'visibility',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'file_size'   => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

### File: `app/Modules/Directory/Models/CompanyContact.php`

> The migration table for company contacts is `company_contact_requests`. The model maps to it explicitly.

```php
<?php

namespace App\Modules\Directory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyContact extends Model
{
    use HasFactory;

    protected $table = 'company_contact_requests';

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

### File: `app/Modules/Directory/Models/CompanyMember.php`

> Maps to the `company_users` pivot/relationship table.

```php
<?php

namespace App\Modules\Directory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyMember extends Model
{
    use HasFactory;

    protected $table = 'company_users';

    protected $fillable = [
        'company_id',
        'user_id',
        'role',
        'title',
        'is_active',
        'joined_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
```

### File: `app/Modules/Directory/Models/Industry.php` (helper, referenced by relationship)

```php
<?php

namespace App\Modules\Directory\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = ['name_fr', 'name_en', 'slug', 'icon', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
```

### File: `app/Modules/Directory/Services/CompanyService.php`

```php
<?php

namespace App\Modules\Directory\Services;

use App\Modules\Directory\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    /**
     * Paginated / searchable listing. Uses Scout when a search term is given,
     * otherwise an Eloquent query with optional facet filters.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        if ($search) {
            $builder = Company::search($search);

            if (!empty($filters['region'])) {
                $builder->where('region_id', $filters['region']);
            }
            if (!empty($filters['status'])) {
                $builder->where('status', $filters['status']);
            }

            return $builder->paginate($perPage);
        }

        $query = Company::query()->where('status', 'active');

        if (!empty($filters['region'])) {
            $query->where('region_id', $filters['region']);
        }
        if (!empty($filters['city'])) {
            $query->where('city_id', $filters['city']);
        }
        if (!empty($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }
        if (array_key_exists('is_featured', $filters)) {
            $query->where('is_featured', (bool) $filters['is_featured']);
        }

        return $query->orderByDesc('is_featured')
            ->orderByDesc('rating_avg')
            ->paginate($perPage);
    }

    /**
     * Create a company and attach the creating user as owner.
     */
    public function create(array $data, string $ownerUserId): Company
    {
        return DB::transaction(function () use ($data, $ownerUserId) {
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['status'] = $data['status'] ?? 'draft';

            $company = Company::create($data);

            $company->members()->create([
                'user_id'   => $ownerUserId,
                'role'      => 'owner',
                'is_active' => true,
                'joined_at' => now(),
            ]);

            return $company->fresh('members');
        });
    }

    public function update(Company $company, array $data): Company
    {
        if (isset($data['name']) && $data['name'] !== $company->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $company->id);
        }

        $company->update($data);

        return $company->fresh();
    }

    public function delete(Company $company): void
    {
        $company->delete(); // soft delete; also removes from Scout index
    }

    /**
     * Determine whether the given user owns the company.
     */
    public function isOwner(Company $company, string $userId): bool
    {
        return $company->members()
            ->where('user_id', $userId)
            ->where('role', 'owner')
            ->exists();
    }

    private function uniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (
            Company::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
```

### Commit

```bash
git add app/Modules/Directory/Models app/Modules/Directory/Services
git commit -m "feat(directory): company models and CompanyService"
```

---

## Task D2-2: Company Directory CRUD Endpoints (TDD)

### Step 1 — Write the failing feature test first

#### File: `tests/Feature/Directory/CompanyTest.php`

```php
<?php

namespace Tests\Feature\Directory;

use App\Models\User;
use App\Modules\Directory\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    private function ownerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('company_owner');

        return $user;
    }

    private function companyPayload(array $overrides = []): array
    {
        return array_merge([
            'name'           => 'Douala Tech SARL',
            'trade_name'     => 'DoualaTech',
            'description_fr' => 'Une entreprise technologique.',
            'description_en' => 'A technology company.',
            'legal_form'     => 'sarl',
            'email'          => 'contact@doualatech.cm',
            'phone'          => '+237600000000',
            'region_id'      => 1,
        ], $overrides);
    }

    /** @test */
    public function public_can_list_companies(): void
    {
        Company::factory()->count(3)->create(['status' => 'active']);

        $response = $this->getJson('/api/v1/companies');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'verification_status']],
                'meta' => ['current_page', 'total'],
            ]);
    }

    /** @test */
    public function owner_can_create_a_company(): void
    {
        Passport::actingAs($this->ownerUser(), ['companies:write']);

        $response = $this->postJson('/api/v1/companies', $this->companyPayload());

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Douala Tech SARL');

        $this->assertDatabaseHas('companies', ['name' => 'Douala Tech SARL']);
    }

    /** @test */
    public function creating_a_company_requires_write_scope(): void
    {
        Passport::actingAs($this->ownerUser(), []); // no scope

        $this->postJson('/api/v1/companies', $this->companyPayload())
            ->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_create_a_company(): void
    {
        $this->postJson('/api/v1/companies', $this->companyPayload())
            ->assertUnauthorized();
    }

    /** @test */
    public function validation_fails_without_required_fields(): void
    {
        Passport::actingAs($this->ownerUser(), ['companies:write']);

        $this->postJson('/api/v1/companies', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'legal_form']);
    }

    /** @test */
    public function public_can_view_a_single_company(): void
    {
        $company = Company::factory()->create(['status' => 'active']);

        $this->getJson("/api/v1/companies/{$company->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $company->id);
    }

    /** @test */
    public function owner_can_update_their_company(): void
    {
        $user = $this->ownerUser();
        Passport::actingAs($user, ['companies:write']);

        $company = Company::factory()->create();
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        $this->putJson("/api/v1/companies/{$company->id}", ['trade_name' => 'NewName'])
            ->assertOk()
            ->assertJsonPath('data.trade_name', 'NewName');
    }

    /** @test */
    public function non_owner_cannot_update_a_company(): void
    {
        Passport::actingAs($this->ownerUser(), ['companies:write']);

        $company = Company::factory()->create(); // current user is not a member

        $this->putJson("/api/v1/companies/{$company->id}", ['trade_name' => 'Hijack'])
            ->assertForbidden();
    }

    /** @test */
    public function owner_can_soft_delete_a_company(): void
    {
        $user = $this->ownerUser();
        Passport::actingAs($user, ['companies:write']);

        $company = Company::factory()->create();
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        $this->deleteJson("/api/v1/companies/{$company->id}")->assertNoContent();

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    /** @test */
    public function search_returns_matching_companies(): void
    {
        Company::factory()->create(['name' => 'Douala Logistics', 'status' => 'active']);
        Company::factory()->create(['name' => 'Yaounde Foods', 'status' => 'active']);

        // Scout "database" driver is used in tests (see phpunit.xml SCOUT_DRIVER).
        $response = $this->getJson('/api/v1/companies?search=Douala');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Douala Logistics'));
    }

    /** @test */
    public function owner_can_list_and_upload_documents(): void
    {
        $user = $this->ownerUser();
        Passport::actingAs($user, ['companies:write']);

        $company = Company::factory()->create();
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        \Illuminate\Support\Facades\Storage::fake('local');

        $this->postJson("/api/v1/companies/{$company->id}/documents", [
            'type'  => 'rccm',
            'title' => 'RCCM Certificate',
            'file'  => \Illuminate\Http\UploadedFile::fake()->create('rccm.pdf', 120, 'application/pdf'),
        ])->assertCreated();

        $this->getJson("/api/v1/companies/{$company->id}/documents")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
```

Run it (expected: red — routes/controllers do not exist yet):

```bash
php artisan test --filter=CompanyTest
# Expected: FAIL — 404 / class not found
```

### Step 2 — Test support (model factory + phpunit env)

#### File: `database/factories/CompanyFactory.php`

```php
<?php

namespace Database\Factories;

use App\Modules\Directory\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name'                => $name,
            'slug'                => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 99999),
            'trade_name'          => $this->faker->companySuffix(),
            'description_fr'      => $this->faker->sentence(),
            'description_en'      => $this->faker->sentence(),
            'legal_form'          => 'sarl',
            'status'              => 'active',
            'verification_status' => 'unverified',
            'email'               => $this->faker->companyEmail(),
            'phone'               => '+2376' . $this->faker->numerify('########'),
            'region_id'           => null,
            'city_id'             => null,
        ];
    }
}
```

> Ensure `Company` uses `HasFactory` and that `Company::newFactory()` resolves. Because the model lives outside `App\Models`, add the resolver to the model:
>
> ```php
> protected static function newFactory(): \Database\Factories\CompanyFactory
> {
>     return \Database\Factories\CompanyFactory::new();
> }
> ```

Confirm `phpunit.xml` forces the test-safe Scout driver:

```xml
<env name="SCOUT_DRIVER" value="database"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Step 3 — Form Requests

#### File: `app/Modules/Directory/Requests/CreateCompanyRequest.php`

```php
<?php

namespace App\Modules\Directory\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'trade_name'     => ['nullable', 'string', 'max:255'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'legal_form'     => ['required', Rule::in(['sarl', 'sa', 'snc', 'scs', 'ge', 'association', 'cooperative', 'other'])],
            'rccm_number'    => ['nullable', 'string', 'max:50', 'unique:companies,rccm_number'],
            'niu_number'     => ['nullable', 'string', 'max:20', 'unique:companies,niu_number'],
            'incorporation_date' => ['nullable', 'date'],
            'share_capital'  => ['nullable', 'integer', 'min:0'],
            'region_id'      => ['nullable', 'integer', 'exists:regions,id'],
            'city_id'        => ['nullable', 'integer', 'exists:cities,id'],
            'address'        => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:255'],
            'website'        => ['nullable', 'url', 'max:255'],
        ];
    }
}
```

#### File: `app/Modules/Directory/Requests/UpdateCompanyRequest.php`

```php
<?php

namespace App\Modules\Directory\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $companyId = $this->route('id');

        return [
            'name'           => ['sometimes', 'string', 'max:255'],
            'trade_name'     => ['nullable', 'string', 'max:255'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'legal_form'     => ['sometimes', Rule::in(['sarl', 'sa', 'snc', 'scs', 'ge', 'association', 'cooperative', 'other'])],
            'rccm_number'    => ['nullable', 'string', 'max:50', Rule::unique('companies', 'rccm_number')->ignore($companyId)],
            'niu_number'     => ['nullable', 'string', 'max:20', Rule::unique('companies', 'niu_number')->ignore($companyId)],
            'incorporation_date' => ['nullable', 'date'],
            'share_capital'  => ['nullable', 'integer', 'min:0'],
            'region_id'      => ['nullable', 'integer', 'exists:regions,id'],
            'city_id'        => ['nullable', 'integer', 'exists:cities,id'],
            'address'        => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:255'],
            'website'        => ['nullable', 'url', 'max:255'],
            'status'         => ['sometimes', Rule::in(['draft', 'pending_verification', 'active', 'suspended', 'dissolved'])],
        ];
    }
}
```

### Step 4 — Resources

#### File: `app/Modules/Directory/Resources/CompanyResource.php`

```php
<?php

namespace App\Modules\Directory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'slug'                => $this->slug,
            'trade_name'          => $this->trade_name,
            'description_fr'      => $this->description_fr,
            'description_en'      => $this->description_en,
            'description'         => $locale === 'en' ? $this->description_en : $this->description_fr,
            'legal_form'          => $this->legal_form,
            'status'              => $this->status,
            'verification_status' => $this->verification_status,
            'rccm_number'         => $this->rccm_number,
            'niu_number'          => $this->niu_number,
            'region_id'           => $this->region_id,
            'city_id'             => $this->city_id,
            'address'             => $this->address,
            'phone'               => $this->phone,
            'email'               => $this->email,
            'website'             => $this->website,
            'logo_url'            => $this->logo_url,
            'cover_url'           => $this->cover_url,
            'is_featured'         => (bool) $this->is_featured,
            'rating_avg'          => (float) $this->rating_avg,
            'rating_count'        => (int) $this->rating_count,
            'view_count'          => (int) $this->view_count,
            'documents'           => CompanyDocumentResource::collection($this->whenLoaded('documents')),
            'members'             => $this->whenLoaded('members'),
            'created_at'          => optional($this->created_at)->toIso8601String(),
            'updated_at'          => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
```

#### File: `app/Modules/Directory/Resources/CompanyDocumentResource.php`

```php
<?php

namespace App\Modules\Directory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'title'       => $this->title,
            'visibility'  => $this->visibility,
            'is_verified' => (bool) $this->is_verified,
            'file_size'   => $this->file_size,
            'mime_type'   => $this->mime_type,
            'created_at'  => optional($this->created_at)->toIso8601String(),
        ];
    }
}
```

#### File: `app/Modules/Directory/Resources/CompanyCollection.php`

```php
<?php

namespace App\Modules\Directory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CompanyCollection extends ResourceCollection
{
    public $collects = CompanyResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total'        => $this->resource->total(),
                'current_page' => $this->resource->currentPage(),
                'per_page'     => $this->resource->perPage(),
                'last_page'    => $this->resource->lastPage(),
            ],
        ];
    }
}
```

### Step 5 — Controllers

#### File: `app/Modules/Directory/Controllers/CompanyController.php`

```php
<?php

namespace App\Modules\Directory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Directory\Models\Company;
use App\Modules\Directory\Requests\CreateCompanyRequest;
use App\Modules\Directory\Requests\UpdateCompanyRequest;
use App\Modules\Directory\Resources\CompanyCollection;
use App\Modules\Directory\Resources\CompanyResource;
use App\Modules\Directory\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companies)
    {
    }

    public function index(Request $request): CompanyCollection
    {
        $paginator = $this->companies->list(
            $request->only(['search', 'region', 'city', 'status', 'verification_status', 'is_featured']),
            (int) $request->integer('per_page', 15)
        );

        return new CompanyCollection($paginator);
    }

    public function store(CreateCompanyRequest $request): JsonResponse
    {
        $company = $this->companies->create($request->validated(), $request->user()->id);

        return (new CompanyResource($company->load('members')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): CompanyResource
    {
        $company = Company::with('documents')->findOrFail($id);
        $company->increment('view_count');

        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, string $id): CompanyResource
    {
        $company = Company::findOrFail($id);

        abort_unless(
            $this->companies->isOwner($company, $request->user()->id),
            403,
            'Only the company owner may update this company.'
        );

        return new CompanyResource($this->companies->update($company, $request->validated()));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        $isAdmin = $request->user()->hasAnyRole(['super_admin', 'govt_reviewer']);

        abort_unless(
            $isAdmin || $this->companies->isOwner($company, $request->user()->id),
            403,
            'Not authorized to delete this company.'
        );

        $this->companies->delete($company);

        return response()->json(null, 204);
    }
}
```

#### File: `app/Modules/Directory/Controllers/CompanyDocumentController.php`

```php
<?php

namespace App\Modules\Directory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Directory\Models\Company;
use App\Modules\Directory\Resources\CompanyDocumentResource;
use App\Modules\Directory\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CompanyDocumentController extends Controller
{
    public function __construct(private readonly CompanyService $companies)
    {
    }

    public function index(string $id): AnonymousResourceCollection
    {
        $company = Company::findOrFail($id);

        return CompanyDocumentResource::collection($company->documents);
    }

    public function store(Request $request, string $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        abort_unless(
            $this->companies->isOwner($company, $request->user()->id),
            403,
            'Only the owner may upload documents.'
        );

        $data = $request->validate([
            'type'       => ['required', Rule::in(['rccm', 'niu', 'statuts', 'ifu', 'cnps', 'cmf_license', 'annual_report', 'other'])],
            'title'      => ['required', 'string', 'max:255'],
            'visibility' => ['nullable', Rule::in(['private', 'verified_only', 'public'])],
            'file'       => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store("companies/{$company->id}/documents", 'local');

        $document = $company->documents()->create([
            'type'       => $data['type'],
            'title'      => $data['title'],
            'visibility' => $data['visibility'] ?? 'private',
            'file_path'  => $path,
            'file_hash'  => hash_file('sha256', $file->getRealPath()),
            'file_size'  => $file->getSize(),
            'mime_type'  => $file->getClientMimeType(),
        ]);

        return (new CompanyDocumentResource($document))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, string $id, int $docId): JsonResponse
    {
        $company = Company::findOrFail($id);

        abort_unless(
            $this->companies->isOwner($company, $request->user()->id),
            403,
            'Only the owner may delete documents.'
        );

        $company->documents()->where('id', $docId)->firstOrFail()->delete();

        return response()->json(null, 204);
    }
}
```

#### File: `app/Modules/Directory/Controllers/CompanyMemberController.php`

```php
<?php

namespace App\Modules\Directory\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Directory\Models\Company;
use App\Modules\Directory\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyMemberController extends Controller
{
    public function __construct(private readonly CompanyService $companies)
    {
    }

    public function index(string $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        return response()->json(['data' => $company->members()->with('user:id,name,email')->get()]);
    }

    public function store(Request $request, string $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        abort_unless(
            $this->companies->isOwner($company, $request->user()->id),
            403,
            'Only the owner may invite members.'
        );

        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role'  => ['required', Rule::in(['admin', 'member', 'viewer'])],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        $member = $company->members()->updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $data['role'], 'title' => $data['title'] ?? null, 'is_active' => true, 'joined_at' => now()]
        );

        return response()->json(['data' => $member], 201);
    }
}
```

### Step 6 — Routes + Provider

#### File: `app/Modules/Directory/Routes/api.php`

```php
<?php

use App\Modules\Directory\Controllers\CompanyController;
use App\Modules\Directory\Controllers\CompanyDocumentController;
use App\Modules\Directory\Controllers\CompanyMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public reads
    Route::get('companies', [CompanyController::class, 'index']);
    Route::get('companies/{id}', [CompanyController::class, 'show']);
    Route::get('companies/{id}/documents', [CompanyDocumentController::class, 'index']);

    // Authenticated writes
    Route::middleware(['auth:api'])->group(function () {
        Route::middleware('scopes:companies:write')->group(function () {
            Route::post('companies', [CompanyController::class, 'store']);
            Route::put('companies/{id}', [CompanyController::class, 'update']);
            Route::delete('companies/{id}', [CompanyController::class, 'destroy']);

            Route::post('companies/{id}/documents', [CompanyDocumentController::class, 'store']);
            Route::delete('companies/{id}/documents/{docId}', [CompanyDocumentController::class, 'destroy']);

            Route::post('companies/{id}/members', [CompanyMemberController::class, 'store']);
        });

        Route::get('companies/{id}/members', [CompanyMemberController::class, 'index']);
    });
});
```

#### File: `app/Modules/Directory/Providers/DirectoryServiceProvider.php` (update existing)

```php
<?php

namespace App\Modules\Directory\Providers;

use App\Modules\Directory\Services\CompanyService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DirectoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CompanyService::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
```

> Confirm `DirectoryServiceProvider` is registered in `bootstrap/providers.php`. If not, add:
> `App\Modules\Directory\Providers\DirectoryServiceProvider::class,`

### Step 7 — Run the tests green

```bash
php artisan test --filter=CompanyTest
# Expected:
# PASS  Tests\Feature\Directory\CompanyTest
#  ✓ public can list companies
#  ✓ owner can create a company
#  ✓ creating a company requires write scope
#  ✓ unauthenticated user cannot create a company
#  ✓ validation fails without required fields
#  ✓ public can view a single company
#  ✓ owner can update their company
#  ✓ non owner cannot update a company
#  ✓ owner can soft delete a company
#  ✓ search returns matching companies
#  ✓ owner can list and upload documents
# Tests: 11 passed
```

### Commit

```bash
git add app/Modules/Directory tests/Feature/Directory database/factories/CompanyFactory.php
git commit -m "feat(directory): company CRUD, documents, members endpoints (TDD)"
```

---

## Task D2-3: Meilisearch Integration for Company Search

- [ ] Configure index settings and verify filtered search works.

### Step 1 — `Company::toSearchableArray()` is already defined (Task D2-1). Confirm filterable/sortable attributes in `config/scout.php`.

#### File: `config/scout.php` (merge the meilisearch index-settings block)

```php
'meilisearch' => [
    'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
    'key'  => env('MEILISEARCH_KEY', null),

    'index-settings' => [
        'companies' => [
            'filterableAttributes' => [
                'region_id',
                'city_id',
                'legal_form',
                'status',
                'verification_status',
                'is_featured',
            ],
            'sortableAttributes' => [
                'rating_avg',
                'view_count',
                'created_at',
            ],
            'searchableAttributes' => [
                'name',
                'trade_name',
                'description_fr',
                'description_en',
            ],
            'rankingRules' => [
                'words',
                'typo',
                'proximity',
                'attribute',
                'sort',
                'exactness',
                'is_featured:desc',
                'rating_avg:desc',
            ],
        ],
    ],
],
```

> Note on industry filter: the `?industry=technology` query maps to `region_id`/`industry` facets. Industries live in a pivot (`company_industry`); for Meilisearch, add an `industry_ids` array to `toSearchableArray()` if industry faceting is required:
>
> ```php
> 'industry_ids' => $this->industries->pluck('id')->all(),
> ```
> and add `industry_ids` to `filterableAttributes`.

### Step 2 — Push settings to Meilisearch and import existing records

```bash
php artisan scout:sync-index-settings
php artisan scout:import "App\Modules\Directory\Models\Company"
```

Expected:

```
Synced settings for [companies].
Imported [App\Modules\Directory\Models\Company] models up to ID ...
```

### Step 3 — Manual smoke test against the running app

```bash
curl -s "http://localhost/api/v1/companies?search=Douala&region=1&verification_status=verified" | jq '.data[].name'
```

> Documented endpoint shape: `GET /api/v1/companies?search=Douala&region=Centre&industry=technology`.
> `region`/`industry` accept IDs in this implementation; if name-based filters are needed, resolve names to IDs in `CompanyService::list()` before building the Scout query.

### Step 4 — Filtered-search test (extend CompanyTest)

Add to `tests/Feature/Directory/CompanyTest.php`:

```php
/** @test */
public function search_can_be_filtered_by_region(): void
{
    Company::factory()->create(['name' => 'Douala Centre Co', 'region_id' => 1, 'status' => 'active']);
    Company::factory()->create(['name' => 'Douala West Co', 'region_id' => 2, 'status' => 'active']);

    $response = $this->getJson('/api/v1/companies?search=Douala&region=1');

    $response->assertOk();
    $this->assertTrue(
        collect($response->json('data'))->every(fn ($c) => $c['region_id'] === 1)
    );
}
```

```bash
php artisan test --filter=search_can_be_filtered_by_region
# Expected: PASS (uses database Scout driver in tests)
```

### Commit

```bash
git add config/scout.php tests/Feature/Directory/CompanyTest.php app/Modules/Directory/Models/Company.php
git commit -m "feat(directory): meilisearch index settings and filtered company search"
```

---

## Task D2-4: Verification Module — Models + VerificationService

- [ ] Create the verification models and `VerificationService`.

### File: `app/Modules/Verification/Models/VerificationApplication.php`

```php
<?php

namespace App\Modules\Verification\Models;

use App\Modules\Directory\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'submitted_by',
        'target_tier_id',
        'status',
        'rejection_reason_fr',
        'rejection_reason_en',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(VerificationTier::class, 'target_tier_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VerificationDocument::class, 'application_id');
    }

    public function checks(): HasMany
    {
        return $this->hasMany(GovernmentRegistryCheck::class, 'application_id');
    }
}
```

### File: `app/Modules/Verification/Models/VerificationTier.php` (helper)

```php
<?php

namespace App\Modules\Verification\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationTier extends Model
{
    protected $fillable = ['name', 'slug', 'description_fr', 'description_en', 'requirements', 'level'];

    protected $casts = ['requirements' => 'array', 'level' => 'integer'];
}
```

### File: `app/Modules/Verification/Models/VerificationDocument.php`

```php
<?php

namespace App\Modules\Verification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'type',
        'file_path',
        'original_name',
        'is_accepted',
        'rejection_reason',
    ];

    protected $casts = ['is_accepted' => 'boolean'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(VerificationApplication::class, 'application_id');
    }
}
```

### File: `app/Modules/Verification/Models/GovernmentRegistryCheck.php`

> Maps to the `verification_checks` table.

```php
<?php

namespace App\Modules\Verification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernmentRegistryCheck extends Model
{
    use HasFactory;

    protected $table = 'verification_checks';

    protected $fillable = [
        'application_id',
        'registry',
        'status',
        'result_data',
        'notes',
        'checked_at',
    ];

    protected $casts = [
        'result_data' => 'array',
        'checked_at'  => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(VerificationApplication::class, 'application_id');
    }
}
```

### File: `app/Modules/Verification/Services/VerificationService.php`

```php
<?php

namespace App\Modules\Verification\Services;

use App\Modules\Directory\Models\Company;
use App\Modules\Verification\Models\GovernmentRegistryCheck;
use App\Modules\Verification\Models\VerificationApplication;
use App\Modules\Verification\Models\VerificationTier;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VerificationService
{
    public function __construct(private readonly GovernmentRegistryService $registry)
    {
    }

    /**
     * Submit (or re-submit) a verification application for a company.
     */
    public function submit(Company $company, string $userId, int $targetTierId): VerificationApplication
    {
        $tier = VerificationTier::findOrFail($targetTierId);

        if ($company->verification_applications()->where('status', 'in_review')->exists()
            ?? $this->hasOpenApplication($company)) {
            throw new RuntimeException('An application is already in review for this company.');
        }

        return DB::transaction(function () use ($company, $userId, $tier) {
            $application = VerificationApplication::create([
                'company_id'     => $company->id,
                'submitted_by'   => $userId,
                'target_tier_id' => $tier->id,
                'status'         => 'submitted',
                'submitted_at'   => now(),
            ]);

            $company->update(['status' => 'pending_verification']);

            // Kick off automated registry checks.
            $this->runRegistryChecks($application, $company);

            return $application->fresh('checks');
        });
    }

    public function status(Company $company): ?VerificationApplication
    {
        return $company->verification_applications()
            ->with(['tier', 'checks', 'documents'])
            ->latest()
            ->first();
    }

    public function approve(VerificationApplication $application, string $reviewerId): VerificationApplication
    {
        return DB::transaction(function () use ($application, $reviewerId) {
            $application->update([
                'status'      => 'approved',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);

            $tier = $application->tier;
            $application->company->update([
                'verification_status' => $tier->slug,           // basic|verified|certified
                'status'              => 'active',
            ]);

            return $application->fresh('company');
        });
    }

    public function reject(VerificationApplication $application, string $reviewerId, string $reasonFr, ?string $reasonEn = null): VerificationApplication
    {
        $application->update([
            'status'              => 'rejected',
            'reviewed_by'         => $reviewerId,
            'reviewed_at'         => now(),
            'rejection_reason_fr' => $reasonFr,
            'rejection_reason_en' => $reasonEn,
        ]);

        return $application->fresh();
    }

    public function requestInfo(VerificationApplication $application, string $reviewerId, string $note): VerificationApplication
    {
        $application->update([
            'status'      => 'in_review',
            'reviewed_by' => $reviewerId,
        ]);

        $application->checks()->create([
            'registry'   => 'rccm',
            'status'     => 'pending',
            'notes'      => $note,
            'checked_at' => null,
        ]);

        return $application->fresh('checks');
    }

    /**
     * Run a registry check for a single registry and persist the result.
     */
    public function requestRegistryCheck(VerificationApplication $application, string $registry, string $queryValue): GovernmentRegistryCheck
    {
        $result = $this->registry->check($registry, $queryValue);

        return $application->checks()->create([
            'registry'    => $registry,
            'status'      => $result['matched'] ? 'passed' : 'failed',
            'result_data' => $result,
            'checked_at'  => now(),
        ]);
    }

    private function runRegistryChecks(VerificationApplication $application, Company $company): void
    {
        $map = [
            'rccm' => $company->rccm_number,
            'niu'  => $company->niu_number,
            'anor' => $company->anor_number,
            'cnps' => $company->cnps_number,
        ];

        foreach ($map as $registry => $value) {
            if (!$value) {
                $application->checks()->create([
                    'registry' => $registry, 'status' => 'skipped', 'checked_at' => now(),
                ]);
                continue;
            }
            $this->requestRegistryCheck($application, $registry, $value);
        }
    }

    private function hasOpenApplication(Company $company): bool
    {
        return VerificationApplication::where('company_id', $company->id)
            ->whereIn('status', ['submitted', 'in_review'])
            ->exists();
    }
}
```

> Add the relationship used above to `Company` (Task D2-1 model):
>
> ```php
> public function verification_applications(): \Illuminate\Database\Eloquent\Relations\HasMany
> {
>     return $this->hasMany(\App\Modules\Verification\Models\VerificationApplication::class);
> }
> ```

### Commit

```bash
git add app/Modules/Verification/Models app/Modules/Verification/Services/VerificationService.php app/Modules/Directory/Models/Company.php
git commit -m "feat(verification): models and VerificationService (submit/review/approve/reject)"
```

---

## Task D2-5: Verification Endpoints (TDD)

### Step 1 — Failing test first

#### File: `tests/Feature/Verification/VerificationTest.php`

```php
<?php

namespace Tests\Feature\Verification;

use App\Models\User;
use App\Modules\Directory\Models\Company;
use App\Modules\Verification\Models\VerificationApplication;
use App\Modules\Verification\Models\VerificationTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    private function seedTiers(): VerificationTier
    {
        VerificationTier::create(['name' => 'Unverified', 'slug' => 'unverified', 'level' => 0]);
        VerificationTier::create(['name' => 'Basic', 'slug' => 'basic', 'level' => 1]);

        return VerificationTier::create(['name' => 'Verified', 'slug' => 'verified', 'level' => 2]);
    }

    private function ownedCompany(User $user): Company
    {
        $company = Company::factory()->create([
            'rccm_number' => 'RC/DLA/2020/B/1234',
            'niu_number'  => 'P012345678901X',
        ]);
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        return $company;
    }

    /** @test */
    public function owner_can_submit_a_verification_application(): void
    {
        $user = User::factory()->create();
        $user->assignRole('company_owner');
        Passport::actingAs($user, ['companies:verify']);

        $tier = $this->seedTiers();
        $company = $this->ownedCompany($user);

        $response = $this->postJson("/api/v1/companies/{$company->id}/verification/submit", [
            'target_tier_id' => $tier->id,
        ]);

        $response->assertCreated()->assertJsonPath('data.status', 'submitted');

        $this->assertDatabaseHas('verification_applications', [
            'company_id' => $company->id,
            'status'     => 'submitted',
        ]);
    }

    /** @test */
    public function submit_requires_verify_scope(): void
    {
        $user = User::factory()->create();
        $user->assignRole('company_owner');
        Passport::actingAs($user, []); // no scope

        $tier = $this->seedTiers();
        $company = $this->ownedCompany($user);

        $this->postJson("/api/v1/companies/{$company->id}/verification/submit", ['target_tier_id' => $tier->id])
            ->assertForbidden();
    }

    /** @test */
    public function owner_can_read_verification_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('company_owner');
        Passport::actingAs($user, ['companies:verify']);

        $tier = $this->seedTiers();
        $company = $this->ownedCompany($user);
        VerificationApplication::create([
            'company_id' => $company->id, 'submitted_by' => $user->id,
            'target_tier_id' => $tier->id, 'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->getJson("/api/v1/companies/{$company->id}/verification/status")
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted');
    }

    /** @test */
    public function reviewer_can_list_pending_verifications(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole('govt_reviewer');
        Passport::actingAs($reviewer, ['admin:compliance']);

        $owner = User::factory()->create();
        $tier = $this->seedTiers();
        $company = $this->ownedCompany($owner);
        VerificationApplication::create([
            'company_id' => $company->id, 'submitted_by' => $owner->id,
            'target_tier_id' => $tier->id, 'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->getJson('/api/v1/admin/verifications')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function reviewer_can_approve_an_application(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole('govt_reviewer');
        Passport::actingAs($reviewer, ['admin:compliance']);

        $owner = User::factory()->create();
        $tier = $this->seedTiers();
        $company = $this->ownedCompany($owner);
        $app = VerificationApplication::create([
            'company_id' => $company->id, 'submitted_by' => $owner->id,
            'target_tier_id' => $tier->id, 'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->postJson("/api/v1/admin/verifications/{$app->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id, 'verification_status' => 'verified',
        ]);
    }

    /** @test */
    public function non_reviewer_cannot_approve(): void
    {
        $user = User::factory()->create();
        $user->assignRole('company_owner');
        Passport::actingAs($user, ['admin:compliance']);

        $tier = $this->seedTiers();
        $company = $this->ownedCompany($user);
        $app = VerificationApplication::create([
            'company_id' => $company->id, 'submitted_by' => $user->id,
            'target_tier_id' => $tier->id, 'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->postJson("/api/v1/admin/verifications/{$app->id}/approve")
            ->assertForbidden();
    }

    /** @test */
    public function reviewer_can_reject_with_reason(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole('govt_reviewer');
        Passport::actingAs($reviewer, ['admin:compliance']);

        $owner = User::factory()->create();
        $tier = $this->seedTiers();
        $company = $this->ownedCompany($owner);
        $app = VerificationApplication::create([
            'company_id' => $company->id, 'submitted_by' => $owner->id,
            'target_tier_id' => $tier->id, 'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->postJson("/api/v1/admin/verifications/{$app->id}/reject", [
            'reason_fr' => 'Documents illisibles.',
        ])->assertOk()->assertJsonPath('data.status', 'rejected');
    }
}
```

Run it (expected red):

```bash
php artisan test --filter=VerificationTest
# Expected: FAIL — routes not defined
```

### Step 2 — Resource

#### File: `app/Modules/Verification/Resources/VerificationResource.php`

```php
<?php

namespace App\Modules\Verification\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'company_id'     => $this->company_id,
            'target_tier_id' => $this->target_tier_id,
            'tier'           => $this->whenLoaded('tier', fn () => [
                'slug'  => $this->tier->slug,
                'level' => $this->tier->level,
            ]),
            'status'              => $this->status,
            'rejection_reason_fr' => $this->rejection_reason_fr,
            'rejection_reason_en' => $this->rejection_reason_en,
            'submitted_at'        => optional($this->submitted_at)->toIso8601String(),
            'reviewed_at'         => optional($this->reviewed_at)->toIso8601String(),
            'checks'              => $this->whenLoaded('checks', fn () => $this->checks->map(fn ($c) => [
                'registry' => $c->registry,
                'status'   => $c->status,
            ])),
            'created_at'          => optional($this->created_at)->toIso8601String(),
        ];
    }
}
```

### Step 3 — Controllers

#### File: `app/Modules/Verification/Controllers/VerificationController.php`

```php
<?php

namespace App\Modules\Verification\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Directory\Models\Company;
use App\Modules\Directory\Services\CompanyService;
use App\Modules\Verification\Resources\VerificationResource;
use App\Modules\Verification\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        private readonly VerificationService $verifications,
        private readonly CompanyService $companies,
    ) {
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        abort_unless(
            $this->companies->isOwner($company, $request->user()->id),
            403,
            'Only the company owner may submit a verification application.'
        );

        $data = $request->validate([
            'target_tier_id' => ['required', 'integer', 'exists:verification_tiers,id'],
        ]);

        $application = $this->verifications->submit($company, $request->user()->id, $data['target_tier_id']);

        return (new VerificationResource($application->load('tier', 'checks')))
            ->response()
            ->setStatusCode(201);
    }

    public function status(Request $request, string $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        abort_unless(
            $this->companies->isOwner($company, $request->user()->id),
            403,
            'Only the company owner may view verification status.'
        );

        $application = $this->verifications->status($company);

        abort_if($application === null, 404, 'No verification application found.');

        return (new VerificationResource($application))->response();
    }
}
```

#### File: `app/Modules/Verification/Controllers/AdminVerificationController.php`

```php
<?php

namespace App\Modules\Verification\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Verification\Models\VerificationApplication;
use App\Modules\Verification\Resources\VerificationResource;
use App\Modules\Verification\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminVerificationController extends Controller
{
    public function __construct(private readonly VerificationService $verifications)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $applications = VerificationApplication::with(['tier', 'company:id,name', 'checks'])
            ->whereIn('status', ['submitted', 'in_review'])
            ->latest('submitted_at')
            ->paginate((int) $request->integer('per_page', 20));

        return VerificationResource::collection($applications);
    }

    public function approve(Request $request, int $appId): JsonResponse
    {
        $application = VerificationApplication::with('tier', 'company')->findOrFail($appId);

        $application = $this->verifications->approve($application, $request->user()->id);

        return (new VerificationResource($application->load('tier')))->response();
    }

    public function reject(Request $request, int $appId): JsonResponse
    {
        $application = VerificationApplication::findOrFail($appId);

        $data = $request->validate([
            'reason_fr' => ['required', 'string'],
            'reason_en' => ['nullable', 'string'],
        ]);

        $application = $this->verifications->reject(
            $application,
            $request->user()->id,
            $data['reason_fr'],
            $data['reason_en'] ?? null
        );

        return (new VerificationResource($application))->response();
    }

    public function requestInfo(Request $request, int $appId): JsonResponse
    {
        $application = VerificationApplication::findOrFail($appId);

        $data = $request->validate(['note' => ['required', 'string']]);

        $application = $this->verifications->requestInfo($application, $request->user()->id, $data['note']);

        return (new VerificationResource($application->load('checks')))->response();
    }
}
```

### Step 4 — Routes + Provider

#### File: `app/Modules/Verification/Routes/api.php`

```php
<?php

use App\Modules\Verification\Controllers\AdminVerificationController;
use App\Modules\Verification\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:api'])->group(function () {
    // Company-owner verification flow
    Route::middleware('scopes:companies:verify')->group(function () {
        Route::post('companies/{id}/verification/submit', [VerificationController::class, 'submit']);
    });

    Route::get('companies/{id}/verification/status', [VerificationController::class, 'status']);

    // Admin / reviewer flow
    Route::middleware('scopes:admin:compliance')->group(function () {
        Route::get('admin/verifications', [AdminVerificationController::class, 'index']);

        Route::middleware('role:govt_reviewer|super_admin')->group(function () {
            Route::post('admin/verifications/{appId}/approve', [AdminVerificationController::class, 'approve']);
            Route::post('admin/verifications/{appId}/reject', [AdminVerificationController::class, 'reject']);
            Route::post('admin/verifications/{appId}/request-info', [AdminVerificationController::class, 'requestInfo']);
        });
    });
});
```

> The `role:` middleware is Spatie's `\Spatie\Permission\Middleware\RoleMiddleware`. Confirm it is aliased as `role` in `bootstrap/app.php` (`$middleware->alias([...])`). All roles are on the `api` guard, which matches `auth:api`.

#### File: `app/Modules/Verification/Providers/VerificationServiceProvider.php` (update existing)

```php
<?php

namespace App\Modules\Verification\Providers;

use App\Modules\Verification\Services\GovernmentRegistryService;
use App\Modules\Verification\Services\VerificationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class VerificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // GovernmentRegistryService binding lives in Task D2-6 config.
        $this->app->singleton(VerificationService::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../../../config/registries.php', 'registries');

        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
```

> Confirm registration in `bootstrap/providers.php`:
> `App\Modules\Verification\Providers\VerificationServiceProvider::class,`

### Step 5 — Run green

```bash
php artisan test --filter=VerificationTest
# Expected:
# PASS  Tests\Feature\Verification\VerificationTest
#  ✓ owner can submit a verification application
#  ✓ submit requires verify scope
#  ✓ owner can read verification status
#  ✓ reviewer can list pending verifications
#  ✓ reviewer can approve an application
#  ✓ non reviewer cannot approve
#  ✓ reviewer can reject with reason
# Tests: 7 passed
```

### Commit

```bash
git add app/Modules/Verification tests/Feature/Verification
git commit -m "feat(verification): submit/status/admin review endpoints (TDD)"
```

---

## Task D2-6: Government Registry Simulation Layer

- [ ] Build the registry abstraction with a mock driver (default) and a stub real-RCCM driver.

### File: `config/registries.php`

```php
<?php

return [
    /*
    | Active driver: "mock" for dev/sandbox, "rccm_api" for the real RCCM.
    */
    'default' => env('REGISTRY_DRIVER', 'mock'),

    'drivers' => [
        'mock' => [
            'class' => \App\Modules\Verification\Services\Drivers\MockRegistryDriver::class,
        ],
        'rccm_api' => [
            'class'    => \App\Modules\Verification\Services\Drivers\RccmApiDriver::class,
            'base_url' => env('RCCM_API_URL', 'https://api.rccm.cm'),
            'api_key'  => env('RCCM_API_KEY'),
        ],
    ],

    // Simulated latency range (ms) for the mock driver.
    'mock' => [
        'min_latency_ms' => 20,
        'max_latency_ms' => 120,
    ],
];
```

### File: `app/Modules/Verification/Services/Contracts/RegistryDriver.php`

```php
<?php

namespace App\Modules\Verification\Services\Contracts;

interface RegistryDriver
{
    /**
     * Look up a single value in a registry.
     *
     * @param  string  $registry  rccm|niu|anor|cnps|cmf
     * @param  string  $queryValue
     * @return array{matched: bool, registry: string, query: string, data: array, response_ms: int}
     */
    public function lookup(string $registry, string $queryValue): array;
}
```

### File: `app/Modules/Verification/Services/GovernmentRegistryService.php`

```php
<?php

namespace App\Modules\Verification\Services;

use App\Modules\Verification\Models\RegistryLookup;
use App\Modules\Verification\Services\Contracts\RegistryDriver;

class GovernmentRegistryService
{
    public function __construct(private readonly RegistryDriver $driver)
    {
    }

    /**
     * Run a registry check, persist the lookup, and return the normalized result.
     *
     * @return array{matched: bool, registry: string, query: string, data: array, response_ms: int}
     */
    public function check(string $registry, string $queryValue): array
    {
        $result = $this->driver->lookup($registry, $queryValue);

        RegistryLookup::create([
            'registry'      => $registry,
            'query_value'   => $queryValue,
            'response_data' => $result['data'],
            'matched'       => $result['matched'],
            'response_code' => $result['matched'] ? 200 : 404,
            'response_ms'   => $result['response_ms'],
        ]);

        return $result;
    }
}
```

### File: `app/Modules/Verification/Models/RegistryLookup.php`

```php
<?php

namespace App\Modules\Verification\Models;

use Illuminate\Database\Eloquent\Model;

class RegistryLookup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'registry', 'query_value', 'response_data', 'matched', 'response_code', 'response_ms', 'looked_up_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'matched'       => 'boolean',
        'looked_up_at'  => 'datetime',
    ];
}
```

### File: `app/Modules/Verification/Services/Drivers/MockRegistryDriver.php`

```php
<?php

namespace App\Modules\Verification\Services\Drivers;

use App\Modules\Verification\Services\Contracts\RegistryDriver;

class MockRegistryDriver implements RegistryDriver
{
    public function lookup(string $registry, string $queryValue): array
    {
        $latency = random_int(
            (int) config('registries.mock.min_latency_ms', 20),
            (int) config('registries.mock.max_latency_ms', 120)
        );

        // Deterministic sandbox rule: any value containing "FAIL" misses;
        // empty values miss; everything else matches.
        $matched = $queryValue !== '' && !str_contains(strtoupper($queryValue), 'FAIL');

        return [
            'matched'     => $matched,
            'registry'    => $registry,
            'query'       => $queryValue,
            'response_ms' => $latency,
            'data'        => $matched ? $this->fakeRecord($registry, $queryValue) : [],
        ];
    }

    private function fakeRecord(string $registry, string $value): array
    {
        return match ($registry) {
            'rccm' => [
                'rccm_number'       => $value,
                'company_name'      => 'SANDBOX ' . strtoupper(substr(md5($value), 0, 6)) . ' SARL',
                'legal_form'        => 'sarl',
                'registration_date' => '2020-01-15',
                'status'            => 'active',
            ],
            'niu' => [
                'niu_number'   => $value,
                'taxpayer_name'=> 'SANDBOX TAXPAYER',
                'tax_center'   => 'CDI Douala',
                'is_compliant' => true,
            ],
            'anor' => [
                'anor_number'      => $value,
                'standard_name'    => 'NC 234:2019',
                'certification_date' => '2022-03-10',
                'expiry_date'      => '2025-03-10',
            ],
            'cnps' => [
                'cnps_number'           => $value,
                'contributions_current' => true,
                'last_payment_date'     => '2026-05-31',
            ],
            'cmf' => [
                'cmf_license'  => $value,
                'license_type' => 'broker',
                'is_active'    => true,
            ],
            default => ['value' => $value],
        };
    }
}
```

### File: `app/Modules/Verification/Services/Drivers/RccmApiDriver.php`

```php
<?php

namespace App\Modules\Verification\Services\Drivers;

use App\Modules\Verification\Services\Contracts\RegistryDriver;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Real RCCM driver. Stubbed for now — wiring is in place but the live
 * endpoint is not yet contracted. Throws if called for non-RCCM registries.
 */
class RccmApiDriver implements RegistryDriver
{
    public function lookup(string $registry, string $queryValue): array
    {
        if ($registry !== 'rccm') {
            throw new RuntimeException("RccmApiDriver only handles the 'rccm' registry, got [{$registry}].");
        }

        $start = microtime(true);

        $response = Http::withToken((string) config('registries.drivers.rccm_api.api_key'))
            ->baseUrl((string) config('registries.drivers.rccm_api.base_url'))
            ->acceptJson()
            ->get('/v1/lookup', ['rccm' => $queryValue]);

        $ms = (int) round((microtime(true) - $start) * 1000);

        return [
            'matched'     => $response->successful() && (bool) $response->json('found', false),
            'registry'    => 'rccm',
            'query'       => $queryValue,
            'response_ms' => $ms,
            'data'        => (array) $response->json('record', []),
        ];
    }
}
```

### Binding the driver — update `VerificationServiceProvider::register()`

```php
public function register(): void
{
    $this->mergeConfigFrom(__DIR__ . '/../../../../config/registries.php', 'registries');

    $this->app->bind(
        \App\Modules\Verification\Services\Contracts\RegistryDriver::class,
        function ($app) {
            $default = config('registries.default', 'mock');
            $class = config("registries.drivers.{$default}.class");

            return $app->make($class);
        }
    );

    $this->app->singleton(\App\Modules\Verification\Services\GovernmentRegistryService::class);
    $this->app->singleton(VerificationService::class);
}
```

### Test: registry simulation

Add `tests/Feature/Verification/RegistryCheckTest.php`:

```php
<?php

namespace Tests\Feature\Verification;

use App\Modules\Verification\Services\GovernmentRegistryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistryCheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function mock_driver_matches_a_valid_rccm_number(): void
    {
        $service = app(GovernmentRegistryService::class);

        $result = $service->check('rccm', 'RC/DLA/2020/B/1234');

        $this->assertTrue($result['matched']);
        $this->assertSame('rccm', $result['registry']);
        $this->assertArrayHasKey('company_name', $result['data']);
        $this->assertDatabaseHas('registry_lookups', [
            'registry' => 'rccm', 'query_value' => 'RC/DLA/2020/B/1234', 'matched' => true,
        ]);
    }

    /** @test */
    public function mock_driver_misses_a_fail_marked_value(): void
    {
        $service = app(GovernmentRegistryService::class);

        $result = $service->check('niu', 'P000000FAIL');

        $this->assertFalse($result['matched']);
        $this->assertSame([], $result['data']);
    }
}
```

```bash
php artisan test --filter=RegistryCheckTest
# Expected:
# PASS  Tests\Feature\Verification\RegistryCheckTest
#  ✓ mock driver matches a valid rccm number
#  ✓ mock driver misses a fail marked value
# Tests: 2 passed
```

### Commit

```bash
git add config/registries.php app/Modules/Verification/Services app/Modules/Verification/Models/RegistryLookup.php tests/Feature/Verification/RegistryCheckTest.php app/Modules/Verification/Providers/VerificationServiceProvider.php
git commit -m "feat(verification): government registry simulation layer (mock + rccm stub)"
```

---

## Task D2-7: Run all Day 2 tests + merge

- [ ] Run the full suite and integrate the branch.

### Step 1 — Full suite

```bash
php artisan test
# Expected (Day 1 + Day 2 combined):
# PASS  Tests\Feature\Auth\* (Day 1)
# PASS  Tests\Feature\Directory\CompanyTest        (12 tests)
# PASS  Tests\Feature\Verification\VerificationTest (7 tests)
# PASS  Tests\Feature\Verification\RegistryCheckTest (2 tests)
# Tests: all green
```

If anything is red, STOP and use superpowers:systematic-debugging before proceeding.

### Step 2 — Sync the search index against real Meilisearch (non-test env)

```bash
php artisan scout:sync-index-settings
php artisan scout:import "App\Modules\Directory\Models\Company"
```

### Step 3 — Merge Day 1 work and branch for Day 2

```bash
# Determine the default branch name
git branch --list master main

git checkout master   # or: git checkout main
git merge feat/auth-endpoints

git checkout -b feat/day2-directory
```

### Step 4 — Confirm per-sub-task commits exist

Each sub-task above ends with its own commit. Verify history:

```bash
git log --oneline -8
# Expected (most recent first):
# feat(verification): government registry simulation layer (mock + rccm stub)
# feat(verification): submit/status/admin review endpoints (TDD)
# feat(verification): models and VerificationService (submit/review/approve/reject)
# feat(directory): meilisearch index settings and filtered company search
# feat(directory): company CRUD, documents, members endpoints (TDD)
# feat(directory): company models and CompanyService
```

### Step 5 — Final route sanity check

```bash
php artisan route:list --path=api/v1/companies
php artisan route:list --path=api/v1/admin/verifications
```

Expected to list all 16 Day 2 endpoints (10 directory + 6 verification).

### Step 6 — Finish the branch

Use superpowers:finishing-a-development-branch to open a PR or merge `feat/day2-directory`.

---

## Definition of Done

- [ ] All four directory models + `CompanyService` created (D2-1)
- [ ] 10 directory endpoints live, `CompanyTest` green (D2-2)
- [ ] Meilisearch index settings synced, filtered search test green (D2-3)
- [ ] Three verification models + `VerificationService` created (D2-4)
- [ ] 6 verification endpoints live, `VerificationTest` green (D2-5)
- [ ] Registry simulation layer with mock + rccm stub, `RegistryCheckTest` green (D2-6)
- [ ] `php artisan test` fully green; branch merged/PR'd (D2-7)
