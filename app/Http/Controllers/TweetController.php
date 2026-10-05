<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Models\TweetImage;
use App\Models\TweetVideo;
use App\Support\TweetImageDisplayGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class TweetController extends Controller
{
    private const MAX_IMAGES = 4;

    private const MAX_TOTAL_BYTES = 35 * 1024 * 1024;

    private const MAX_VIDEO_KILOBYTES = 524288;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tweets = Tweet::with(['user', 'liked', 'images', 'video'])->latest()->get();

        return view('tweets.index', compact('tweets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tweets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tweet' => 'required|max:255',
            'images' => 'nullable|array|max:'.self::MAX_IMAGES,
            'images.*' => 'file|mimes:jpg,jpeg,png,gif,webp|max:35840',
            'video' => 'nullable|file|mimes:mp4,webm,mov|max:'.self::MAX_VIDEO_KILOBYTES,
        ]);

        $this->assertTotalImageSizeWithinLimit($request->file('images', []), 0);

        $tweet = $request->user()->tweets()->create($request->only('tweet'));
        $this->storeUploadedImages($tweet, $request->file('images', []));
        $this->storeUploadedVideo($tweet, $request->file('video'));

        return redirect()->route('tweets.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tweet $tweet)
    {
        $tweet->load(['user', 'liked', 'comments.user', 'images', 'video']);

        return view('tweets.show', compact('tweet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tweet $tweet)
    {
        $tweet->load(['images', 'video']);

        return view('tweets.edit', compact('tweet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tweet $tweet)
    {
        $request->validate([
            'tweet' => 'required|max:255',
            'keep_image_ids' => 'nullable|array',
            'keep_image_ids.*' => 'integer',
            'images' => 'nullable|array|max:'.self::MAX_IMAGES,
            'images.*' => 'file|mimes:jpg,jpeg,png,gif,webp|max:35840',
            'keep_video' => 'nullable|boolean',
            'video' => 'nullable|file|mimes:mp4,webm,mov|max:'.self::MAX_VIDEO_KILOBYTES,
        ]);

        $keepIds = collect($request->input('keep_image_ids', []))
            ->map(fn ($id) => (int) $id)
            ->all();

        $keptImages = $tweet->images()->whereIn('id', $keepIds)->get();
        $newFiles = $request->file('images', []) ?? [];

        if (count($keptImages) + count($newFiles) > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'images' => __('ui.tweets.images_max', ['max' => self::MAX_IMAGES]),
            ]);
        }

        $keptBytes = $keptImages->sum('size');
        $this->assertTotalImageSizeWithinLimit($newFiles, $keptBytes);

        $tweet->update($request->only('tweet'));

        $tweet->images()
            ->whereNotIn('id', $keptImages->pluck('id'))
            ->get()
            ->each(fn (TweetImage $image) => $image->delete());

        $this->storeUploadedImages($tweet, $newFiles, $keptImages->count());
        $this->syncVideoOnUpdate($tweet, $request);

        return redirect()->route('tweets.show', $tweet);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tweet $tweet)
    {
        $tweet->delete();

        return redirect()->route('tweets.index');
    }

    /**
     * Search for tweets containing the keyword.
     */
    public function search(Request $request)
    {
        $tweets = Tweet::with(['user', 'liked', 'images', 'video'])
            ->keyword($request->keyword)
            ->latest()
            ->paginate(10);

        return view('tweets.search', compact('tweets'));
    }

    /**
     * @param  array<int, UploadedFile|null>  $files
     */
    private function assertTotalImageSizeWithinLimit(array $files, int $existingBytes): void
    {
        $uploadBytes = collect($files)
            ->filter()
            ->sum(fn (UploadedFile $file) => $file->getSize() ?: 0);

        if (($existingBytes + $uploadBytes) > self::MAX_TOTAL_BYTES) {
            throw ValidationException::withMessages([
                'images' => __('ui.tweets.images_total_max', ['max' => '35MB']),
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile|null>  $files
     */
    private function storeUploadedImages(Tweet $tweet, array $files, int $startOrder = 0): void
    {
        $order = $startOrder;
        $generator = new TweetImageDisplayGenerator;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $stored = $generator->store($file, $tweet->id);

            $tweet->images()->create([
                ...$stored,
                'sort_order' => $order,
            ]);

            $order++;
        }
    }

    private function storeUploadedVideo(Tweet $tweet, ?UploadedFile $file): void
    {
        if (! $file instanceof UploadedFile) {
            return;
        }

        $path = $file->store('tweets/'.$tweet->id.'/videos', 'public');

        $tweet->video()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);
    }

    private function syncVideoOnUpdate(Tweet $tweet, Request $request): void
    {
        $newVideo = $request->file('video');

        if ($newVideo instanceof UploadedFile) {
            $tweet->video?->delete();
            $this->storeUploadedVideo($tweet, $newVideo);

            return;
        }

        if ($tweet->video instanceof TweetVideo && ! $request->boolean('keep_video')) {
            $tweet->video->delete();
        }
    }
}
