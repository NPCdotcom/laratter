<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class TweetImageDisplayGenerator
{
    public const MAX_EDGE = 960;

    /**
     * @return array{path: string, display_path: string|null, original_name: string|null, mime_type: string|null, size: int}
     */
    public function store(UploadedFile $file, int $tweetId): array
    {
        $disk = Storage::disk('public');
        $directory = 'tweets/'.$tweetId;
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $basename = Str::uuid()->toString();

        $path = $file->storeAs($directory, $basename.'.'.$extension, 'public');
        $displayPath = $directory.'/display/'.$basename.'.'.$extension;

        $created = $this->createDisplayVersion(
            $disk->path($path),
            $disk->path($displayPath),
            $file->getMimeType() ?: $file->getClientMimeType()
        );

        return [
            'path' => $path,
            'display_path' => $created ? $displayPath : null,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ];
    }

    public function createDisplayVersion(string $sourceAbsolutePath, string $destinationAbsolutePath, ?string $mimeType): bool
    {
        try {
            $binary = file_get_contents($sourceAbsolutePath);
            if ($binary === false) {
                return false;
            }

            $source = @imagecreatefromstring($binary);
            if ($source === false) {
                return false;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            if ($width < 1 || $height < 1) {
                imagedestroy($source);

                return false;
            }

            $destinationDirectory = dirname($destinationAbsolutePath);
            if (! is_dir($destinationDirectory) && ! mkdir($destinationDirectory, 0755, true) && ! is_dir($destinationDirectory)) {
                imagedestroy($source);

                return false;
            }

            $maxEdge = self::MAX_EDGE;
            $needsResize = $width > $maxEdge || $height > $maxEdge;

            if ($needsResize) {
                $scale = min($maxEdge / $width, $maxEdge / $height);
                $targetWidth = max(1, (int) round($width * $scale));
                $targetHeight = max(1, (int) round($height * $scale));
                $resized = imagescale($source, $targetWidth, $targetHeight);
                imagedestroy($source);

                if ($resized === false) {
                    return false;
                }

                $source = $resized;
            }

            $mime = strtolower((string) $mimeType);
            $saved = match (true) {
                str_contains($mime, 'png') || str_ends_with($destinationAbsolutePath, '.png') => $this->savePng($source, $destinationAbsolutePath),
                str_contains($mime, 'webp') || str_ends_with($destinationAbsolutePath, '.webp') => $this->saveWebp($source, $destinationAbsolutePath),
                str_contains($mime, 'gif') || str_ends_with($destinationAbsolutePath, '.gif') => imagegif($source, $destinationAbsolutePath),
                default => imagejpeg($source, $destinationAbsolutePath, 82),
            };

            imagedestroy($source);

            return (bool) $saved;
        } catch (Throwable) {
            return false;
        }
    }

    private function savePng(\GdImage $source, string $destinationAbsolutePath): bool
    {
        imagealphablending($source, false);
        imagesavealpha($source, true);

        return imagepng($source, $destinationAbsolutePath, 6);
    }

    private function saveWebp(\GdImage $source, string $destinationAbsolutePath): bool
    {
        if (! function_exists('imagewebp')) {
            return imagejpeg($source, preg_replace('/\.webp$/i', '.jpg', $destinationAbsolutePath) ?: $destinationAbsolutePath, 82);
        }

        return imagewebp($source, $destinationAbsolutePath, 82);
    }
}
