<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TweetImage extends Model
{
    protected $fillable = [
        'tweet_id',
        'path',
        'display_path',
        'original_name',
        'mime_type',
        'size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function tweet(): BelongsTo
    {
        return $this->belongsTo(Tweet::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function displayUrl(): string
    {
        $displayPath = $this->display_path;

        if (is_string($displayPath) && $displayPath !== '' && Storage::disk('public')->exists($displayPath)) {
            return Storage::disk('public')->url($displayPath);
        }

        return $this->url();
    }

    protected static function booted(): void
    {
        static::deleting(function (TweetImage $image): void {
            $disk = Storage::disk('public');
            $disk->delete($image->path);

            if (is_string($image->display_path) && $image->display_path !== '' && $image->display_path !== $image->path) {
                $disk->delete($image->display_path);
            }
        });
    }
}
