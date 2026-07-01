<?php

namespace App\Http\Controllers;

use App\Modules\Notifications\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationWebController extends Controller
{
    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    public function index(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login');
        }

        $notifications = UserNotification::where('user_id', $siacUser['id'])
            ->latest()
            ->paginate(20);

        UserNotification::where('user_id', $siacUser['id'])
            ->unread()
            ->update(['read_at' => now()]);

        return view('pages.dashboard.notifications', compact('lang', 'notifications'));
    }
}
