<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const FOLLOW_STATUS_PENDING = 'pending';

    public const FOLLOW_STATUS_ACCEPTED = 'accepted';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'auto_accept_follows',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'auto_accept_follows' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function tweets()
    {
        return $this->hasMany(Tweet::class);
    }

    public function likes()
    {
        return $this->belongsToMany(Tweet::class)->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Users this user follows (any status).
     */
    public function follows(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follow_id', 'follower_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Users who follow this user (any status).
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'follow_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Users this user follows (accepted only).
     */
    public function acceptedFollows(): BelongsToMany
    {
        return $this->follows()->wherePivot('status', self::FOLLOW_STATUS_ACCEPTED);
    }

    /**
     * Users who follow this user (accepted only).
     */
    public function acceptedFollowers(): BelongsToMany
    {
        return $this->followers()->wherePivot('status', self::FOLLOW_STATUS_ACCEPTED);
    }

    public function followStatusTowards(User $user): ?string
    {
        $relation = $this->follows()->where('follower_id', $user->id)->first();

        return $relation?->pivot?->status;
    }

    public function isFollowing(User $user): bool
    {
        return $this->followStatusTowards($user) === self::FOLLOW_STATUS_ACCEPTED;
    }

    public function hasPendingFollowRequestTo(User $user): bool
    {
        return $this->followStatusTowards($user) === self::FOLLOW_STATUS_PENDING;
    }
}
