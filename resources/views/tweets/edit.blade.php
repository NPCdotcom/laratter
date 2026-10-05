<x-layouts.app :title="__('ui.tweets.edit_title')">
  <div class="p-6">
    <a href="{{ route('tweets.show', $tweet) }}" class="text-blue-500 hover:text-blue-700">{{ __('ui.common.back_to_details') }}</a>
    <form method="POST" action="{{ route('tweets.update', $tweet) }}" class="mt-4" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="mb-4">
        <label for="tweet" class="block text-sm font-bold mb-2">{{ __('ui.tweets.edit_tweet_label') }}</label>
        <input type="text" name="tweet" id="tweet" value="{{ old('tweet', $tweet->tweet) }}" class="border rounded w-full py-2 px-3 dark:bg-gray-700">
        @error('tweet')
        <span class="text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
      </div>

      @if ($tweet->images->isNotEmpty())
      <div class="mb-4">
        <p class="mb-2 text-sm font-bold">{{ __('ui.tweets.current_images') }}</p>
        <p class="mb-2 text-xs text-gray-500">{{ __('ui.tweets.keep_images_help') }}</p>
        <div class="grid grid-cols-2 gap-3">
          @foreach ($tweet->images as $image)
          <label class="block rounded-lg border p-2 dark:border-gray-600">
            <img src="{{ $image->displayUrl() }}" alt="{{ $image->original_name }}" class="mb-2 max-h-40 w-full rounded object-contain">
            <input
              type="checkbox"
              name="keep_image_ids[]"
              value="{{ $image->id }}"
              class="me-1"
              @checked(collect(old('keep_image_ids', $tweet->images->pluck('id')->all()))->contains($image->id))
            >
            <span class="text-sm">{{ __('ui.tweets.keep_image') }}</span>
          </label>
          @endforeach
        </div>
      </div>
      @endif

      <div class="mb-4">
        <label for="images" class="block text-sm font-bold mb-2">{{ __('ui.tweets.add_images') }}</label>
        <p class="mb-2 text-xs text-gray-500">{{ __('ui.tweets.images_help') }}</p>
        <input type="file" name="images[]" id="images" accept="image/jpeg,image/png,image/gif,image/webp,.gif" multiple class="block w-full text-sm">
        @error('images')
        <span class="text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
        @error('images.*')
        <span class="text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
      </div>

      @if ($tweet->video)
      <div class="mb-4">
        <p class="mb-2 text-sm font-bold">{{ __('ui.tweets.current_video') }}</p>
        <div class="mb-2 overflow-hidden rounded-lg border p-2 dark:border-gray-600">
          <video controls preload="metadata" class="mb-2 max-h-48 w-full" src="{{ $tweet->video->url() }}"></video>
          <label class="inline-flex items-center text-sm">
            <input
              type="checkbox"
              name="keep_video"
              value="1"
              class="me-1"
              @checked(old('keep_video', true))
            >
            <span>{{ __('ui.tweets.keep_video') }}</span>
          </label>
        </div>
      </div>
      @endif

      <div class="mb-4">
        <label for="video" class="block text-sm font-bold mb-2">{{ __('ui.tweets.add_video') }}</label>
        <p class="mb-2 text-xs text-gray-500">{{ __('ui.tweets.video_help') }}</p>
        <input type="file" name="video" id="video" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov" class="block w-full text-sm">
        @error('video')
        <span class="text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
      </div>

      <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">{{ __('ui.common.update') }}</button>
    </form>
  </div>
</x-layouts.app>
