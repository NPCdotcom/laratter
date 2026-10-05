<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\TweetVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TweetVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_tweet_with_video(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $video = UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4');

        $response = $this->post(route('tweets.store'), [
            'tweet' => 'tweet with video',
            'video' => $video,
        ]);

        $response->assertRedirect(route('tweets.index'));

        $tweet = Tweet::first();
        $this->assertNotNull($tweet);
        $this->assertDatabaseCount('tweet_videos', 1);
        $this->assertNotNull($tweet->video);
        Storage::disk('public')->assertExists($tweet->video->path);
    }

    public function test_rejects_video_over_512mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $video = UploadedFile::fake()->create('huge.mp4', 524289, 'video/mp4');

        $response = $this->from(route('tweets.create'))->post(route('tweets.store'), [
            'tweet' => 'oversized video',
            'video' => $video,
        ]);

        $response->assertRedirect(route('tweets.create'));
        $response->assertSessionHasErrors('video');
        $this->assertDatabaseCount('tweets', 0);
        $this->assertDatabaseCount('tweet_videos', 0);
    }

    public function test_streams_video_with_byte_range_support(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $payload = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ012345';
        $video = TweetVideo::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/videos/stream.mp4',
            'original_name' => 'stream.mp4',
            'mime_type' => 'video/mp4',
            'size' => strlen($payload),
        ]);
        Storage::disk('public')->put($video->path, $payload);

        $full = $this->get(route('tweets.video', $tweet));
        $full->assertOk();
        $full->assertHeader('Accept-Ranges', 'bytes');
        $full->assertHeader('Content-Type', 'video/mp4');
        $this->assertSame($payload, $full->streamedContent());

        $partial = $this->get(route('tweets.video', $tweet), [
            'Range' => 'bytes=0-4',
        ]);
        $partial->assertStatus(206);
        $partial->assertHeader('Content-Range');
        $this->assertSame('ABCDE', $partial->streamedContent());
    }

    public function test_can_keep_existing_video_on_update(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $video = TweetVideo::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/videos/keep.mp4',
            'original_name' => 'keep.mp4',
            'mime_type' => 'video/mp4',
            'size' => 2048,
        ]);
        Storage::disk('public')->put($video->path, 'keep-video');

        $response = $this->put(route('tweets.update', $tweet), [
            'tweet' => 'updated with kept video',
            'keep_video' => '1',
        ]);

        $response->assertRedirect(route('tweets.show', $tweet));
        $this->assertDatabaseHas('tweet_videos', ['id' => $video->id]);
        Storage::disk('public')->assertExists($video->path);
    }

    public function test_can_replace_video_on_update(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $old = TweetVideo::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/videos/old.mp4',
            'original_name' => 'old.mp4',
            'mime_type' => 'video/mp4',
            'size' => 2048,
        ]);
        Storage::disk('public')->put($old->path, 'old-video');

        $response = $this->put(route('tweets.update', $tweet), [
            'tweet' => 'updated with new video',
            'keep_video' => '1',
            'video' => UploadedFile::fake()->create('new.mp4', 512, 'video/mp4'),
        ]);

        $response->assertRedirect(route('tweets.show', $tweet));
        $this->assertDatabaseMissing('tweet_videos', ['id' => $old->id]);
        Storage::disk('public')->assertMissing($old->path);

        $tweet->refresh();
        $this->assertNotNull($tweet->video);
        Storage::disk('public')->assertExists($tweet->video->path);
    }

    public function test_can_remove_video_on_update(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $video = TweetVideo::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/videos/remove.mp4',
            'original_name' => 'remove.mp4',
            'mime_type' => 'video/mp4',
            'size' => 2048,
        ]);
        Storage::disk('public')->put($video->path, 'remove-video');

        $response = $this->put(route('tweets.update', $tweet), [
            'tweet' => 'updated without video',
            // keep_video intentionally omitted
        ]);

        $response->assertRedirect(route('tweets.show', $tweet));
        $this->assertDatabaseMissing('tweet_videos', ['id' => $video->id]);
        Storage::disk('public')->assertMissing($video->path);
    }

    public function test_deleting_tweet_removes_video_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $video = TweetVideo::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/videos/delete-me.mp4',
            'original_name' => 'delete-me.mp4',
            'mime_type' => 'video/mp4',
            'size' => 2048,
        ]);
        Storage::disk('public')->put($video->path, 'delete-me');

        $response = $this->delete(route('tweets.destroy', $tweet));

        $response->assertRedirect(route('tweets.index'));
        $this->assertDatabaseMissing('tweets', ['id' => $tweet->id]);
        $this->assertDatabaseMissing('tweet_videos', ['id' => $video->id]);
        Storage::disk('public')->assertMissing($video->path);
    }

    public function test_tweet_index_shows_video_player(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id, 'tweet' => 'video tweet']);
        $video = TweetVideo::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/videos/show.mp4',
            'original_name' => 'show.mp4',
            'mime_type' => 'video/mp4',
            'size' => 2048,
        ]);
        Storage::disk('public')->put($video->path, 'show-video');

        $this->get(route('tweets.index'))
            ->assertOk()
            ->assertSee($video->url(), false)
            ->assertSee('video tweet');
    }
}
