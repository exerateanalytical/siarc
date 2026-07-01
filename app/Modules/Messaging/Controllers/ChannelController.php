<?php

namespace App\Modules\Messaging\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function __construct(private readonly ConversationService $service) {}

    public function authenticate(Request $request, int $conversationId): JsonResponse
    {
        $conversation = Conversation::findOrFail($conversationId);

        if (! $this->service->isParticipant($conversation, $request->user())) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['auth' => true]);
    }
}
