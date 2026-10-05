<x-layouts.app :title="__('ui.comments.show_title')">
  <div class="p-6">
    <a href="{{ route('tweets.show', $tweet) }}" class="text-blue-500 hover:text-blue-700">{{ __('ui.comments.back_to_tweet') }}</a>
    <p class="text-sm text-gray-500 mt-2">{{ $tweet->tweet }}: {{ $tweet->user->name }}</p>
    <p class="text-lg mt-2">{{ $comment->comment }}</p>
    <p class="text-sm text-gray-500">{{ $comment->user->name }}</p>
    <p class="text-sm text-gray-500">{{ __('ui.comments.comment_created_at') }}: {{ $comment->created_at->format('Y-m-d H:i') }}</p>
    <p class="text-sm text-gray-500">{{ __('ui.comments.comment_updated_at') }}: {{ $comment->updated_at->format('Y-m-d H:i') }}</p>
    @if (auth()->id() == $comment->user_id)
    <div class="flex mt-4">
      <a href="{{ route('tweets.comments.edit', [$tweet, $comment]) }}" class="text-blue-500 hover:text-blue-700 mr-2">{{ __('ui.common.edit') }}</a>
      <form action="{{ route('tweets.comments.destroy', [$tweet, $comment]) }}" method="POST" onsubmit="return confirm(@js(__('ui.common.confirm_delete')));">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-500 hover:text-red-700">{{ __('ui.common.delete') }}</button>
      </form>
    </div>
    @endif
  </div>
</x-layouts.app>
