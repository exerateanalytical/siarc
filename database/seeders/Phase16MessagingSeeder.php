<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase16MessagingSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('conversations')->count() > 0) {
            $this->command->info('Conversations already exist — skipping.');
            return;
        }
        $users = DB::table('users')->orderBy('created_at')->limit(2)->pluck('id')->all();
        if (count($users) < 2) { $this->command->warn('Need 2 users.'); return; }
        [$a, $b] = $users;
        [$one, $two] = strcmp($a, $b) < 0 ? [$a, $b] : [$b, $a];
        $cid = DB::table('conversations')->insertGetId([
            'user_one_id' => $one, 'user_two_id' => $two,
            'last_message_at' => now()->subMinutes(5), 'created_at' => now()->subHours(2), 'updated_at' => now()->subMinutes(5),
        ]);
        $thread = [
            [$a, 'Hello! I came across your profile and was impressed by your experience. We have an opening that could be a great fit.', now()->subHours(2)],
            [$b, 'Thank you for reaching out! I would love to hear more about the role.', now()->subHours(1)->subMinutes(50)],
            [$a, 'Great — it is a senior engineering position based in Douala, with a focus on fintech. Are you open to a quick call this week?', now()->subHours(1)->subMinutes(40)],
            [$b, 'Absolutely. I am available Wednesday or Thursday afternoon. Shall I send my latest CV?', now()->subMinutes(5)],
        ];
        foreach ($thread as $i => [$sender, $body, $at]) {
            DB::table('messages')->insert([
                'conversation_id' => $cid, 'sender_id' => $sender, 'body' => $body,
                // last message left unread for the recipient so the badge shows
                'read_at' => $i < 3 ? now() : null,
                'created_at' => $at,
            ]);
        }
        $this->command->info('Phase 16: seeded 1 conversation with 4 messages.');
    }
}
