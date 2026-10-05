<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\TweetImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TweetImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_tweet_with_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $images = [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.gif'),
        ];

        $response = $this->post(route('tweets.store'), [
            'tweet' => 'tweet with images',
            'images' => $images,
        ]);

        $response->assertRedirect(route('tweets.index'));

        $tweet = Tweet::first();
        $this->assertNotNull($tweet);
        $this->assertDatabaseCount('tweet_images', 2);
        $this->assertCount(2, $tweet->images);

        foreach ($tweet->images as $image) {
            Storage::disk('public')->assertExists($image->path);
            $this->assertNotNull($image->display_path);
            Storage::disk('public')->assertExists($image->display_path);
            $this->assertNotSame($image->path, $image->display_path);
        }
    }

    public function test_rejects_more_than_four_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $images = [
            UploadedFile::fake()->image('1.jpg'),
            UploadedFile::fake()->image('2.jpg'),
            UploadedFile::fake()->image('3.jpg'),
            UploadedFile::fake()->image('4.jpg'),
            UploadedFile::fake()->image('5.jpg'),
        ];

        $response = $this->from(route('tweets.create'))->post(route('tweets.store'), [
            'tweet' => 'too many images',
            'images' => $images,
        ]);

        $response->assertRedirect(route('tweets.create'));
        $response->assertSessionHasErrors('images');
        $this->assertDatabaseCount('tweets', 0);
        $this->assertDatabaseCount('tweet_images', 0);
    }

    public function test_rejects_total_image_size_over_35mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $images = [
            UploadedFile::fake()->create('big1.jpg', 20 * 1024, 'image/jpeg'),
            UploadedFile::fake()->create('big2.jpg', 16 * 1024, 'image/jpeg'),
        ];

        $response = $this->from(route('tweets.create'))->post(route('tweets.store'), [
            'tweet' => 'oversized images',
            'images' => $images,
        ]);

        $response->assertRedirect(route('tweets.create'));
        $response->assertSessionHasErrors('images');
        $this->assertDatabaseCount('tweets', 0);
    }

    public function test_can_update_tweet_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id]);

        $kept = TweetImage::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/keep.jpg',
            'original_name' => 'keep.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'sort_order' => 0,
        ]);
        Storage::disk('public')->put($kept->path, 'keep');

        $removed = TweetImage::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/remove.jpg',
            'original_name' => 'remove.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'sort_order' => 1,
        ]);
        Storage::disk('public')->put($removed->path, 'remove');

        $response = $this->put(route('tweets.update', $tweet), [
            'tweet' => 'updated with images',
            'keep_image_ids' => [$kept->id],
            'images' => [
                UploadedFile::fake()->image('new.png'),
            ],
        ]);

        $response->assertRedirect(route('tweets.show', $tweet));

        $tweet->refresh();
        $this->assertSame('updated with images', $tweet->tweet);
        $this->assertCount(2, $tweet->images);
        $this->assertTrue($tweet->images->contains('id', $kept->id));
        $this->assertDatabaseMissing('tweet_images', ['id' => $removed->id]);
        Storage::disk('public')->assertMissing($removed->path);
        Storage::disk('public')->assertExists($kept->path);

        $newImage = $tweet->images->firstWhere('id', '!=', $kept->id);
        $this->assertNotNull($newImage);
        Storage::disk('public')->assertExists($newImage->path);
        $this->assertNotNull($newImage->display_path);
        Storage::disk('public')->assertExists($newImage->display_path);
    }

    public function test_deleting_tweet_removes_image_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $image = TweetImage::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/delete-me.jpg',
            'display_path' => 'tweets/'.$tweet->id.'/display/delete-me.jpg',
            'original_name' => 'delete-me.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 2048,
            'sort_order' => 0,
        ]);
        Storage::disk('public')->put($image->path, 'delete-me');
        Storage::disk('public')->put($image->display_path, 'delete-me-display');

        $response = $this->delete(route('tweets.destroy', $tweet));

        $response->assertRedirect(route('tweets.index'));
        $this->assertDatabaseMissing('tweets', ['id' => $tweet->id]);
        $this->assertDatabaseMissing('tweet_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
        Storage::disk('public')->assertMissing($image->display_path);
    }

    public function test_tweet_list_uses_display_image_and_exposes_original(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $tweet = Tweet::factory()->create(['user_id' => $user->id, 'tweet' => 'preview tweet']);
        $image = TweetImage::create([
            'tweet_id' => $tweet->id,
            'path' => 'tweets/'.$tweet->id.'/original.jpg',
            'display_path' => 'tweets/'.$tweet->id.'/display/original.jpg',
            'original_name' => 'original.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 2048,
            'sort_order' => 0,
        ]);
        Storage::disk('public')->put($image->path, 'original-bytes');
        Storage::disk('public')->put($image->display_path, 'display-bytes');

        $response = $this->get(route('tweets.index'));
        $html = str_replace('\/', '/', $response->getContent());

        $response->assertOk();
        $this->assertStringContainsString($image->displayUrl(), $html);
        $this->assertStringContainsString($image->url(), $html);
        $this->assertNotSame($image->displayUrl(), $image->url());
    }
}
