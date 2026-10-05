<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\FollowAccepted;
use App\Notifications\FollowReceived;
use App\Notifications\FollowRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function store(User $user): RedirectResponse
    {
        $actor = auth()->user();

        if ($actor->id === $user->id) {
            abort(403);
        }

        if ($actor->follows()->where('follower_id', $user->id)->exists()) {
            return back();
        }

        $status = $user->auto_accept_follows
            ? User::FOLLOW_STATUS_ACCEPTED
            : User::FOLLOW_STATUS_PENDING;

        $actor->follows()->attach($user->id, ['status' => $status]);

        if ($status === User::FOLLOW_STATUS_ACCEPTED) {
            $user->notify(new FollowReceived($actor));
        } else {
            $user->notify(new FollowRequested($actor));
        }

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        auth()->user()->follows()->detach($user->id);

        return back();
    }

    public function accept(User $user): RedirectResponse
    {
        $me = auth()->user();

        $updated = DB::table('follows')
            ->where('follower_id', $me->id)
            ->where('follow_id', $user->id)
            ->where('status', User::FOLLOW_STATUS_PENDING)
            ->update([
                'status' => User::FOLLOW_STATUS_ACCEPTED,
                'updated_at' => now(),
            ]);

        if ($updated) {
            $this->markFollowRequestNotificationsHandled($me, $user);
            $user->notify(new FollowAccepted($me));
        }

        return back();
    }

    public function reject(User $user): RedirectResponse
    {
        $me = auth()->user();

        DB::table('follows')
            ->where('follower_id', $me->id)
            ->where('follow_id', $user->id)
            ->where('status', User::FOLLOW_STATUS_PENDING)
            ->delete();

        $this->markFollowRequestNotificationsHandled($me, $user);

        return back();
    }

    private function markFollowRequestNotificationsHandled(User $notifiable, User $actor): void
    {
        $notifiable->notifications()
            ->where('type', FollowRequested::class)
            ->get()
            ->filter(fn ($notification) => (int) ($notification->data['actor_id'] ?? 0) === $actor->id)
            ->each(function ($notification): void {
                $data = $notification->data;
                $data['actionable'] = false;
                $data['handled'] = true;

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update([
                        'data' => json_encode($data),
                        'read_at' => now(),
                        'updated_at' => now(),
                    ]);
            });
    }
}
