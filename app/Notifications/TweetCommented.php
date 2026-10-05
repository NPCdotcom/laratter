<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TweetCommented extends Notification
{
    use Queueable;

    public function __construct(public User $actor, public Tweet $tweet, public Comment $comment) {}

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
            'category' => 'general',
            'type' => 'tweet_commented',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'tweet_id' => $this->tweet->id,
            'comment_id' => $this->comment->id,
            'tweet_excerpt' => mb_strimwidth($this->tweet->tweet, 0, 40, '…'),
            'message_key' => 'ui.notifications.tweet_commented',
            'url' => route('tweets.show', $this->tweet),
        ];
    }
}
