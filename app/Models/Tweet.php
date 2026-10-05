<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tweet extends Model
{
    /** @use HasFactory<\Database\Factories\TweetFactory> */
    use HasFactory;

    protected $fillable = ['tweet'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function liked()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function images(): HasMany
    {
        return $this->hasMany(TweetImage::class)->orderBy('sort_order');
    }

    public function video(): HasOne
    {
        return $this->hasOne(TweetVideo::class);
    }

    public function scopeKeyword(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, function (Builder $query, string $keyword) {
            $query->where('tweet', 'like', '%' . $keyword . '%');
        });
    }

    public function scopeTimeline(Builder $query, User $user): Builder
    {
        return $query
            ->where('user_id', $user->id)
            ->orWhereIn('user_id', $user->acceptedFollows()->pluck('users.id'));
    }

    protected static function booted(): void
    {
        static::deleting(function (Tweet $tweet): void {
            $tweet->images()->each(function (TweetImage $image): void {
                $image->delete();
            });

            $tweet->video?->delete();
        });
    }
}
