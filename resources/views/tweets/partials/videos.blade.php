@php
  $compact = $compact ?? false;
@endphp
@if ($tweet->video)
  <div class="mt-2 w-full min-w-0 overflow-hidden rounded-lg bg-zinc-200/70 dark:bg-zinc-800 {{ $compact ? 'h-56' : 'max-h-72' }}">
    <video
      controls
      preload="metadata"
      class="h-full w-full object-contain"
      src="{{ $tweet->video->url() }}"
    >
      {{ __('ui.tweets.video_unsupported') }}
    </video>
  </div>
@endif
