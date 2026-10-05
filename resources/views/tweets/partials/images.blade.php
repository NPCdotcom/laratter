@php
  $compact = $compact ?? false;
  $isMulti = $tweet->images->count() > 1;
@endphp
@if ($tweet->images->isNotEmpty())
  <div
    class="mt-2 min-w-0"
    x-data="{
      open: false,
      src: '',
      alt: '',
      show(src, alt) {
        this.src = src;
        this.alt = alt;
        this.open = true;
      },
      hide() {
        this.open = false;
        this.src = '';
        this.alt = '';
      }
    }"
    x-on:keydown.escape.window="if (open) hide()"
  >
    <div class="grid w-full min-w-0 gap-1.5 {{ $isMulti ? 'grid-cols-2' : 'grid-cols-1' }} {{ $compact && $isMulti ? 'auto-rows-[10rem] sm:auto-rows-[11rem]' : '' }}">
      @foreach ($tweet->images as $image)
        <button
          type="button"
          class="flex min-h-0 min-w-0 w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-zinc-200/70 dark:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500 {{ $isMulti ? ($compact ? 'h-full' : 'aspect-square') : ($compact ? 'h-56' : 'max-h-72') }}"
          x-on:click="show(@js($image->url()), @js($image->original_name ?: __('ui.tweets.image_label')))"
        >
          <img
            src="{{ $image->displayUrl() }}"
            alt="{{ $image->original_name ?: __('ui.tweets.image_label') }}"
            class="h-full max-h-full w-full max-w-full {{ $isMulti || $compact ? 'object-cover' : 'max-h-72 object-contain' }}"
            loading="lazy"
            decoding="async"
          >
        </button>
      @endforeach
    </div>

    <div
      x-show="open"
      x-cloak
      class="fixed inset-0 z-50 flex flex-col p-4"
      role="dialog"
      aria-modal="true"
      aria-label="{{ __('ui.tweets.image_label') }}"
    >
      <div class="absolute inset-0 bg-black/80" x-on:click="hide()"></div>

      <div class="relative z-10 mb-3 flex shrink-0 justify-end">
        <button
          type="button"
          class="rounded-full bg-white/90 px-3 py-1 text-sm font-bold text-gray-900 hover:bg-white"
          x-on:click="hide()"
        >
          {{ __('ui.common.close') }}
        </button>
      </div>

      <div class="relative z-10 flex min-h-0 flex-1 items-center justify-center">
        <img
          :src="src"
          :alt="alt"
          class="max-h-full max-w-full object-contain"
        >
      </div>
    </div>
  </div>
@endif
