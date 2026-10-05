<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use App\Notifications\FollowReceived;
use App\Notifications\TweetCommented;
use App\Notifications\TweetLiked;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_notifies_tweet_owner(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $liker = User::factory()->create();
        $tweet = Tweet::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($liker)
            ->post(route('tweets.like', $tweet))
            ->assertRedirect();

        Notification::assertSentTo($owner, TweetLiked::class);
    }

    public function test_comment_notifies_tweet_owner(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $tweet = Tweet::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($commenter)
            ->post(route('tweets.comments.store', $tweet), [
                'comment' => 'nice tweet',
            ])
            ->assertRedirect(route('tweets.show', $tweet));

        Notification::assertSentTo($owner, TweetCommented::class);
    }

    public function test_notifications_page_lists_follow_and_general_tabs(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();

        $user->notify(new FollowReceived($actor));

        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $user->notify(new TweetLiked($actor, $tweet));

        $this->actingAs($user);

        $this->get(route('notifications.index', ['tab' => 'follow']))
            ->assertOk()
            ->assertSee(__('ui.notifications.tab_follow'))
            ->assertSee($actor->name);

        $this->get(route('notifications.index', ['tab' => 'general']))
            ->assertOk()
            ->assertSee(__('ui.notifications.tab_general'));
    }
}
