<x-layouts.app :title="__('ui.tweets.search_title')">
  <div class="mx-auto w-full max-w-2xl p-4 sm:p-6">
    <h2 class="mb-3 text-lg font-semibold sm:text-xl">{{ __('ui.tweets.search_title') }}</h2>

    <form action="{{ route('tweets.search') }}" method="GET" class="mb-6">
      <div class="flex items-center gap-3">
        <input
          type="text"
          name="keyword"
          class="w-full rounded border px-3 py-2 leading-tight text-gray-700 shadow focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-300"
          placeholder="{{ __('ui.tweets.search_placeholder') }}"
          value="{{ request('keyword') }}"
        >
        <button type="submit" class="shrink-0 rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-700">
          {{ __('ui.tweets.search_button') }}
        </button>
      </div>
    </form>

    @if ($tweets->count())
      <div class="mb-4">
        {{ $tweets->appends(request()->input())->links() }}
      </div>

      @foreach ($tweets as $tweet)
        @include('tweets.partials.card', ['tweet' => $tweet])
      @endforeach

      <div class="mt-4">
        {{ $tweets->appends(request()->input())->links() }}
      </div>
    @else
      <p>{{ __('ui.common.no_tweets_found') }}</p>
    @endif
  </div>
</x-layouts.app>
