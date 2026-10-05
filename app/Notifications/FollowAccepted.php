<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowAccepted extends Notification
{
    use Queueable;

    public function __construct(public User $actor) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'category' => 'follow',
            'type' => 'follow_accepted',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'message_key' => 'ui.notifications.follow_accepted',
        ];
    }
}
