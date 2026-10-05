<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use App\Notifications\FollowAccepted;
use App\Notifications\FollowReceived;
use App\Notifications\FollowRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_follow_a_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $followedUser = User::factory()->create(['auto_accept_follows' => true]);
        $this->post(route('follow.store', $followedUser));

        $this->assertDatabaseHas('follows', [
            'follow_id' => $user->id,
            'follower_id' => $followedUser->id,
            'status' => User::FOLLOW_STATUS_ACCEPTED,
        ]);

        Notification::assertSentTo($followedUser, FollowReceived::class);
    }

    public function test_follow_request_when_auto_accept_disabled(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $followedUser = User::factory()->create(['auto_accept_follows' => false]);
        $this->post(route('follow.store', $followedUser));

        $this->assertDatabaseHas('follows', [
            'follow_id' => $user->id,
            'follower_id' => $followedUser->id,
            'status' => User::FOLLOW_STATUS_PENDING,
        ]);

        Notification::assertSentTo($followedUser, FollowRequested::class);
        $this->assertFalse($user->isFollowing($followedUser));
    }

    public function test_can_accept_follow_request(): void
    {
        Notification::fake();

        $actor = User::factory()->create();
        $target = User::factory()->create(['auto_accept_follows' => false]);

        $actor->follows()->attach($target->id, ['status' => User::FOLLOW_STATUS_PENDING]);

        $this->actingAs($target)
            ->post(route('follow.accept', $actor))
            ->assertRedirect();

        $this->assertDatabaseHas('follows', [
            'follow_id' => $actor->id,
            'follower_id' => $target->id,
            'status' => User::FOLLOW_STATUS_ACCEPTED,
        ]);

        Notification::assertSentTo($actor, FollowAccepted::class);
    }

    public function test_can_reject_follow_request(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['auto_accept_follows' => false]);

        $actor->follows()->attach($target->id, ['status' => User::FOLLOW_STATUS_PENDING]);

        $this->actingAs($target)
            ->delete(route('follow.reject', $actor))
            ->assertRedirect();

        $this->assertDatabaseMissing('follows', [
            'follow_id' => $actor->id,
            'follower_id' => $target->id,
        ]);
    }

    public function test_can_unfollow_a_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $followedUser = User::factory()->create();
        $user->follows()->attach($followedUser->id, ['status' => User::FOLLOW_STATUS_ACCEPTED]);
        $this->delete(route('follow.destroy', $followedUser));

        $this->assertDatabaseMissing('follows', [
            'follow_id' => $user->id,
            'follower_id' => $followedUser->id,
        ]);
    }

    public function test_cannot_follow_self(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('follow.store', $user))->assertForbidden();
    }

    public function test_displays_the_user_and_followings_tweet_at_current_user_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $followedUser = User::factory()->create();
        $user->follows()->attach($followedUser->id, ['status' => User::FOLLOW_STATUS_ACCEPTED]);

        $pendingUser = User::factory()->create();
        $user->follows()->attach($pendingUser->id, ['status' => User::FOLLOW_STATUS_PENDING]);

        $otherUser = User::factory()->create();

        $userTweet = Tweet::factory()->create(['user_id' => $user->id]);
        $followedTweet = Tweet::factory()->create(['user_id' => $followedUser->id]);
        $pendingTweet = Tweet::factory()->create(['user_id' => $pendingUser->id]);
        $otherTweet = Tweet::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('profile.show', $user));

        $response->assertStatus(200);
        $response->assertSee($userTweet->tweet);
        $response->assertSee($followedTweet->tweet);
        $response->assertSee($followedUser->name);
        $response->assertDontSee($pendingTweet->tweet);
        $response->assertDontSee($otherTweet->tweet);
    }

    public function test_displays_the_another_user_tweet_at_user_show_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $anotherUser = User::factory()->create();

        $userTweet = Tweet::factory()->create(['user_id' => $user->id]);
        $anotherTweet = Tweet::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->get(route('profile.show', $anotherUser));

        $response->assertStatus(200);
        $response->assertSee($anotherTweet->tweet);
        $response->assertSee($anotherUser->name);
        $response->assertDontSee($userTweet->tweet);
    }
}
