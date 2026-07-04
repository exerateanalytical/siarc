<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Batch D pages (support ticket detail, notifications centre/detail) read real
// data. Seed a few tickets + replies and notifications for existing users, and
// verification documents for the seeded KYC applications.
return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->limit(6)->pluck('id')->all();
        if (empty($users)) return;
        $now = now();

        // Support tickets (skip if already plentiful)
        if (DB::table('support_tickets')->count() < 4) {
            $tickets = [
                ['Vérification de compte artisan', 'in_progress', 'high'],
                ['Problème de paiement sur commande', 'open', 'medium'],
                ['Question sur les frais de commission', 'open', 'low'],
                ['Demande de modification de boutique', 'resolved', 'medium'],
            ];
            foreach ($tickets as $i => [$subj, $status, $prio]) {
                $uid = $users[$i % count($users)];
                $tid = DB::table('support_tickets')->insertGetId([
                    'uuid' => (string) Str::uuid(), 'user_id' => $uid,
                    'subject_fr' => $subj, 'subject_en' => $subj,
                    'status' => $status, 'priority' => $prio,
                    'created_at' => $now->copy()->subDays($i + 1), 'updated_at' => $now->copy()->subDays($i),
                ]);
                DB::table('support_ticket_replies')->insert([
                    ['ticket_id' => $tid, 'user_id' => $uid, 'body_fr' => 'Bonjour, j\'ai besoin d\'aide concernant : ' . $subj . '. Pouvez-vous vérifier et me donner une mise à jour ? Merci.', 'body_en' => 'Hello, I need help regarding: ' . $subj . '.', 'is_staff' => false, 'created_at' => $now->copy()->subDays($i + 1), 'updated_at' => $now->copy()->subDays($i + 1)],
                    ['ticket_id' => $tid, 'user_id' => $uid, 'body_fr' => 'Bonjour, merci pour votre message. Nous avons bien reçu votre demande et notre équipe la traite actuellement. Nous reviendrons vers vous dès que possible. Cordialement, Équipe Support — Galerie Virtuelle.', 'body_en' => 'Hello, thank you for your message. Our team is handling your request.', 'is_staff' => true, 'created_at' => $now->copy()->subDays($i)->addHours(1), 'updated_at' => $now->copy()->subDays($i)->addHours(1)],
                ]);
            }
        }

        // Notifications
        if (DB::table('user_notifications')->count() < 6) {
            $notifs = [
                ['support', 'Nouveau ticket créé', 'Un nouveau ticket a été créé par un artisan.', '/tableau-de-bord/admin/support'],
                ['message', 'Nouveau message sur un ticket', 'Un agent a répondu au ticket.', '/tableau-de-bord/admin/support'],
                ['article', 'Nouvel article publié', 'L\'article "Comment vérifier un compte artisan" a été publié.', '/actualites'],
                ['announcement', 'Annonce importante', 'Mise à jour de notre politique de confidentialité.', '/actualites'],
                ['account', 'Nouveau compte agent créé', 'Un nouvel agent a été ajouté.', '/tableau-de-bord/admin/utilisateurs'],
                ['reminder', 'Rappel : Ticket en attente', '3 tickets sont en attente de votre réponse.', '/tableau-de-bord/admin/support'],
            ];
            foreach ($notifs as $i => [$type, $title, $body, $link]) {
                DB::table('user_notifications')->insert([
                    'user_id' => $users[0], 'type' => $type, 'title' => $title, 'body' => $body, 'link' => $link,
                    'read_at' => $i < 2 ? null : $now->copy()->subDays($i),
                    'created_at' => $now->copy()->subDays($i)->subHours($i), 'updated_at' => $now->copy()->subDays($i),
                ]);
            }
        }

        // Verification documents for seeded applications (design shows 6 docs)
        if (DB::table('verification_documents')->count() < 6) {
            $app = DB::table('verification_applications')->orderBy('id')->first();
            if ($app) {
                $docs = [
                    ['type' => 'id_director', 'name' => 'Pièce d\'identité — CNI Recto', 'status' => 'accepted'],
                    ['type' => 'id_director', 'name' => 'Pièce d\'identité — CNI Verso', 'status' => 'accepted'],
                    ['type' => 'other', 'name' => 'Photo de profil', 'status' => 'accepted'],
                    ['type' => 'other', 'name' => 'Preuve de domicile — Facture ENEO', 'status' => 'accepted'],
                    ['type' => 'product_cert', 'name' => 'Preuve d\'activité — Photos atelier', 'status' => 'pending'],
                    ['type' => 'other', 'name' => 'Références — Lettre recommandation', 'status' => 'pending'],
                ];
                foreach ($docs as $d) {
                    DB::table('verification_documents')->insert([
                        'application_id' => $app->id, 'type' => $d['type'], 'file_path' => 'verifications/placeholder.pdf',
                        'original_name' => $d['name'], 'status' => $d['status'], 'notes' => null,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Leave seeded data (indistinguishable from organic once live).
    }
};
