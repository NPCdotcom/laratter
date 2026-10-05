<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'general');
        if (! in_array($tab, ['general', 'follow'], true)) {
            $tab = 'general';
        }

        $user = $request->user();
        $unread = $user->unreadNotifications;

        $unreadGeneral = $unread->filter(fn ($notification) => ($notification->data['category'] ?? null) === 'general')->count();
        $unreadFollow = $unread->filter(fn ($notification) => ($notification->data['category'] ?? null) === 'follow')->count();

        $notifications = $user->notifications()
            ->where('data->category', $tab)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unread
            ->filter(fn ($notification) => ($notification->data['category'] ?? null) === $tab)
            ->each
            ->markAsRead();

        return view('notifications.index', [
            'tab' => $tab,
            'notifications' => $notifications,
            'unreadGeneral' => $unreadGeneral,
            'unreadFollow' => $unreadFollow,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
