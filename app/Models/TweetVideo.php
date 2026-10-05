<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TweetVideo extends Model
{
    protected $fillable = [
        'tweet_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function tweet(): BelongsTo
    {
        return $this->belongsTo(Tweet::class);
    }

    public function url(): string
    {
        return route('tweets.video', $this->tweet_id);
    }

    protected static function booted(): void
    {
        static::deleting(function (TweetVideo $video): void {
            Storage::disk('public')->delete($video->path);
        });
    }
}
