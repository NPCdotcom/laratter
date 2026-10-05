<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Notifications\TweetLiked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TweetLikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Tweet $tweet)
    {
        $user = Auth::user();

        if (! $tweet->liked()->where('users.id', $user->id)->exists()) {
            $tweet->liked()->attach($user->id);

            if ($tweet->user_id !== $user->id) {
                $tweet->user->notify(new TweetLiked($user, $tweet));
            }
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tweet $tweet)
    {
        //
        $tweet->liked()->detach(Auth::id());
        return back();
    }
}
