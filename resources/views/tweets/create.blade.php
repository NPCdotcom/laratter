<x-layouts.app :title="__('ui.tweets.create_title')">
  <div class="p-6">
    <h2 class="font-semibold text-xl mb-4">{{ __('ui.tweets.create_title') }}</h2>
    <form
      method="POST"
      action="{{ route('tweets.store') }}"
      enctype="multipart/form-data"
      x-data="{
        tweetText: @js(old('tweet', '')),
        previews: [],
        videoPreview: null,
        onSelect(event) {
          this.previews.forEach((preview) => URL.revokeObjectURL(preview.url));
          this.previews = Array.from(event.target.files || []).map((file) => ({
            name: file.name,
            url: URL.createObjectURL(file),
          }));
        },
        clear() {
          this.previews.forEach((preview) => URL.revokeObjectURL(preview.url));
          this.previews = [];
          this.$refs.imageInput.value = '';
        },
        onSelectVideo(event) {
          if (this.videoPreview) {
            URL.revokeObjectURL(this.videoPreview.url);
          }
          const file = event.target.files?.[0] || null;
          this.videoPreview = file
            ? { name: file.name, url: URL.createObjectURL(file) }
            : null;
        },
        clearVideo() {
          if (this.videoPreview) {
            URL.revokeObjectURL(this.videoPreview.url);
          }
          this.videoPreview = null;
          this.$refs.videoInput.value = '';
        }
      }"
    >
      @csrf
      <div class="mb-4">
        <label for="tweet" class="block text-sm font-bold mb-2">{{ __('ui.tweets.tweet_label') }}</label>
        <input
          type="text"
          name="tweet"
          id="tweet"
          x-model="tweetText"
          class="border rounded w-full py-2 px-3 dark:bg-gray-700"
        >
        @error('tweet')
        <span class="text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <p class="mb-2 text-sm font-bold">{{ __('ui.tweets.images_label') }}</p>
        <p class="mb-3 text-xs text-gray-500">{{ __('ui.tweets.images_help') }}</p>

        <input
          type="file"
          name="images[]"
          id="images"
          x-ref="imageInput"
          accept="image/jpeg,image/png,image/gif,image/webp,.gif"
          multiple
          class="sr-only"
          x-on:change="onSelect($event)"
        >

        <div class="flex flex-wrap items-center gap-3">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700"
            x-on:click="$refs.imageInput.click()"
          >
            <span x-text="previews.length ? @js(__('ui.tweets.change_images')) : @js(__('ui.tweets.select_images'))"></span>
          </button>

          <button
            type="button"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
            x-show="previews.length"
            x-cloak
            x-on:click="clear()"
          >
            {{ __('ui.tweets.clear_images') }}
          </button>

          <span class="text-xs text-gray-500" x-show="previews.length" x-cloak x-text="`{{ __('ui.tweets.selected_count', ['count' => ':count']) }}`.replace(':count', previews.length)"></span>
        </div>

        @error('images')
        <span class="mt-2 block text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
        @error('images.*')
        <span class="mt-2 block text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <p class="mb-2 text-sm font-bold">{{ __('ui.tweets.video_label') }}</p>
        <p class="mb-3 text-xs text-gray-500">{{ __('ui.tweets.video_help') }}</p>

        <input
          type="file"
          name="video"
          id="video"
          x-ref="videoInput"
          accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov"
          class="sr-only"
          x-on:change="onSelectVideo($event)"
        >

        <div class="flex flex-wrap items-center gap-3">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700"
            x-on:click="$refs.videoInput.click()"
          >
            <span x-text="videoPreview ? @js(__('ui.tweets.change_video')) : @js(__('ui.tweets.select_video'))"></span>
          </button>

          <button
            type="button"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
            x-show="videoPreview"
            x-cloak
            x-on:click="clearVideo()"
          >
            {{ __('ui.tweets.clear_video') }}
          </button>

          <span class="truncate text-xs text-gray-500" x-show="videoPreview" x-cloak x-text="videoPreview?.name"></span>
        </div>

        @error('video')
        <span class="mt-2 block text-red-500 text-xs italic">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4" x-show="tweetText.trim().length || previews.length || videoPreview" x-cloak>
        <p class="mb-2 text-sm font-bold">{{ __('ui.tweets.preview_title') }}</p>
        <div class="mb-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
          <p x-text="tweetText" class="min-h-[1.5rem] whitespace-pre-wrap break-words"></p>

          <div
            class="mt-2 grid gap-2"
            :class="previews.length > 1 ? 'grid-cols-2' : 'grid-cols-1'"
            x-show="previews.length"
          >
            <template x-for="preview in previews" :key="preview.url">
              <div
                class="flex w-full items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700"
                :class="previews.length > 1 ? 'aspect-square' : 'max-h-72'"
              >
                <img
                  :src="preview.url"
                  :alt="preview.name"
                  :class="previews.length > 1 ? 'h-full w-full object-cover' : 'max-h-72 w-full object-contain'"
                >
              </div>
            </template>
          </div>

          <div class="mt-2 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700" x-show="videoPreview" x-cloak>
            <video
              controls
              preload="metadata"
              class="max-h-72 w-full"
              x-bind:src="videoPreview?.url"
            ></video>
          </div>

          <p class="text-sm text-gray-500">{{ __('ui.common.author') }}: {{ auth()->user()->name }}</p>
          <span class="text-blue-500 hover:text-blue-700">{{ __('ui.common.view_details') }}</span>
          <div class="flex mt-2">
            <span class="text-blue-500 hover:text-blue-700">{{ __('ui.tweets.like') }} 0</span>
          </div>
        </div>
      </div>

      <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">{{ __('ui.tweets.tweet_label') }}</button>
    </form>
  </div>
</x-layouts.app>
