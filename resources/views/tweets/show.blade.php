<x-layouts.app :title="__('ui.tweets.show_title')">
  <div class="mx-auto w-full max-w-2xl p-4 sm:p-6">
    <a href="{{ route('tweets.index') }}" class="text-blue-500 hover:text-blue-700">{{ __('ui.common.back_to_list') }}</a>

    <div class="mt-3">
      @include('tweets.partials.card', [
        'tweet' => $tweet,
        'clamp' => false,
        'hideDetailsLink' => true,
        'showMeta' => true,
      ])
    </div>

    @if (auth()->id() === $tweet->user_id)
      <div class="mb-4 flex gap-3">
        @include('tweets.partials.owner-actions', ['tweet' => $tweet])
      </div>
    @endif

    <div class="mt-2">
      <p class="text-sm text-gray-500">{{ __('ui.tweets.comment_count') }} {{ $tweet->comments->count() }}</p>
      <a href="{{ route('tweets.comments.create', $tweet) }}" class="text-blue-500 hover:text-blue-700">{{ __('ui.tweets.add_comment') }}</a>
    </div>

    <div class="mt-4 space-y-2">
      @foreach ($tweet->comments as $comment)
        <a href="{{ route('tweets.comments.show', [$tweet, $comment]) }}" class="block rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
          <p class="break-words text-base font-medium">{{ $comment->comment }}</p>
          <span class="text-sm text-gray-500">{{ $comment->user->name }} {{ $comment->created_at->format('Y-m-d H:i') }}</span>
        </a>
      @endforeach
    </div>
  </div>
</x-layouts.app>
