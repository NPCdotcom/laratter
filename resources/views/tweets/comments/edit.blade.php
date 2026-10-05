<x-layouts.app :title="__('ui.comments.edit_title')">
  <div class="p-6">
    <a href="{{ route('tweets.comments.show', [$tweet, $comment]) }}" class="text-blue-500 hover:text-blue-700">{{ __('ui.common.back_to_details') }}</a>
    <form method="POST" action="{{ route('tweets.comments.update', [$tweet, $comment]) }}" class="mt-4">
      @csrf
      @method('PUT')
      <div class="mb-4">
        <label for="comment" class="block text-sm font-bold mb-2">{{ __('ui.comments.edit_comment_label') }}</label>
        <input type="text" name="comment" id="comment" value="{{ $comment->comment }}" class="border rounded w-full py-2 px-3 dark:bg-gray-700">
        @error('comment')
        <span class="text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
      </div>
      <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">{{ __('ui.common.update') }}</button>
    </form>
  </div>
</x-layouts.app>
