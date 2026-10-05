<x-layouts.app :title="__('ui.profile.show_title')">
  <div class="mx-auto w-full max-w-2xl p-4 sm:p-6">
    <h2 class="mb-3 text-lg font-semibold sm:text-xl">{{ __('ui.profile.show_title') }}</h2>
    <a href="{{ route('tweets.index') }}" class="mr-2 text-blue-500 hover:text-blue-700">{{ __('ui.common.back_to_list') }}</a>
    <p class="mt-2 text-lg font-medium">{{ $user->name }}</p>
    <div class="text-sm text-gray-500">
      <p>{{ __('ui.profile.account_created_at') }}: {{ $user->created_at->format('Y-m-d H:i') }}</p>
    </div>
    @if ($user->id !== auth()->id())
    @php
      $followStatus = auth()->user()->followStatusTowards($user);
    @endphp
    <div class="mt-2">
      @if ($followStatus === \App\Models\User::FOLLOW_STATUS_ACCEPTED)
      <form action="{{ route('follow.destroy', $user) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-500 hover:text-red-700">{{ __('ui.profile.unfollow') }}</button>
      </form>
      @elseif ($followStatus === \App\Models\User::FOLLOW_STATUS_PENDING)
      <form action="{{ route('follow.destroy', $user) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-amber-600 hover:text-amber-700">{{ __('ui.profile.cancel_request') }}</button>
      </form>
      @else
      <form action="{{ route('follow.store', $user) }}" method="POST">
        @csrf
        <button type="submit" class="text-blue-500 hover:text-blue-700">{{ __('ui.profile.follow') }}</button>
      </form>
      @endif
    </div>
    @endif

    <p class="mt-2">{{ __('ui.profile.following') }}: {{ $user->accepted_follows_count }}</p>
    <p>{{ __('ui.profile.followers') }}: {{ $user->accepted_followers_count }}</p>

    @if ($tweets->count())
      <div class="my-4">
        {{ $tweets->appends(request()->input())->links() }}
      </div>

      @foreach ($tweets as $tweet)
        @include('tweets.partials.card', ['tweet' => $tweet])
      @endforeach

      <div class="mt-4">
        {{ $tweets->appends(request()->input())->links() }}
      </div>
    @else
      <p class="mt-4">{{ __('ui.common.no_tweets_found') }}</p>
    @endif
  </div>
</x-layouts.app>
