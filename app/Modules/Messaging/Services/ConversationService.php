<?php

namespace App\Modules\Messaging\Services;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Messaging\Events\MessageSent;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Models\Message;
use App\Modules\Messaging\Models\MessageAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConversationService
{
    public function findOrCreate(User $buyer, Business $business, ?int $productId, string $subject): Conversation
    {
        return Conversation::firstOrCreate(
            [
                'buyer_id'    => $buyer->id,
                'business_id' => $business->id,
                'product_id'  => $productId,
            ],
            [
                'subject'         => $subject,
                'status'          => 'active',
                'last_message_at' => now(),
            ]
        );
    }

    public function sendMessage(Conversation $conversation, User $sender, string $body, array $attachmentFiles = []): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'body'            => $body,
        ]);

        foreach ($attachmentFiles as $file) {
            /** @var UploadedFile $file */
            $path = "messaging/{$conversation->id}/" . Str::uuid() . '.' . $file->getClientOriginalExtension();
            Storage::disk(config('filesystems.default') === 's3' ? 's3' : 'public')->put($path, $file->getContent());

            MessageAttachment::create([
                'message_id'    => $message->id,
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size'     => $file->getSize(),
                'mime_type'     => $file->getMimeType(),
            ]);
        }

        $message->load('attachments');

        $conversation->update(['last_message_at' => now(), 'status' => 'active']);

        broadcast(new MessageSent($message));

        return $message;
    }

    public function markRead(Conversation $conversation, User $user): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function isParticipant(Conversation $conversation, User $user): bool
    {
        if ($conversation->buyer_id === $user->id) return true;
        if ($conversation->business?->user_id === $user->id) return true;
        return false;
    }
}
