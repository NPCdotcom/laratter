<x-layouts.app :title="__('ui.tweets.index_title')">
  <div class="mx-auto w-full max-w-2xl p-4 sm:p-6">
    <h2 class="mb-3 text-lg font-semibold sm:text-xl">{{ __('ui.tweets.index_title') }}</h2>
    @foreach ($tweets as $tweet)
      @include('tweets.partials.card', ['tweet' => $tweet])
    @endforeach
  </div>
</x-layouts.app>
