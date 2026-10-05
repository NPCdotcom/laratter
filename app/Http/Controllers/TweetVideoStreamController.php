<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TweetVideoStreamController extends Controller
{
    public function show(Tweet $tweet): BinaryFileResponse
    {
        $video = $tweet->video;

        if ($video === null || ! Storage::disk('public')->exists($video->path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($video->path);
        $mime = $video->mime_type ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
