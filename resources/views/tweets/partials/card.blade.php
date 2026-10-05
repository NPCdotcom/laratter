@php
  $clamp = $clamp ?? true;
@endphp
<div class="mb-3 w-full min-w-0 rounded-lg bg-gray-100 p-3 dark:bg-gray-700 sm:mb-4 sm:p-4">
  <p class="break-words text-lg font-medium leading-relaxed text-gray-900 dark:text-gray-100 {{ $clamp ? 'line-clamp-3' : '' }}">
    {{ $tweet->tweet }}
  </p>

  @include('tweets.partials.images', ['tweet' => $tweet, 'compact' => true])
  @include('tweets.partials.videos', ['tweet' => $tweet, 'compact' => true])

  <a href="{{ route('profile.show', $tweet->user) }}">
    <p class="mt-1 text-sm text-gray-500">{{ __('ui.common.author') }}: {{ $tweet->user->name }}</p>
  </a>

  @isset($showMeta)
    @if ($showMeta)
      <p class="text-sm text-gray-500">{{ __('ui.common.created_at') }}: {{ $tweet->created_at->format('Y-m-d H:i') }}</p>
      <p class="text-sm text-gray-500">{{ __('ui.common.updated_at') }}: {{ $tweet->updated_at->format('Y-m-d H:i') }}</p>
    @endif
  @endisset

  @unless ($hideDetailsLink ?? false)
    <a href="{{ route('tweets.show', $tweet) }}" class="text-blue-500 hover:text-blue-700">{{ __('ui.common.view_details') }}</a>
  @endunless

  <div class="mt-2 flex flex-wrap items-center gap-3">
    @if ($tweet->liked->contains(auth()->id()))
      <form action="{{ route('tweets.dislike', $tweet) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-500 hover:text-red-700">{{ __('ui.tweets.dislike') }} {{ $tweet->liked->count() }}</button>
      </form>
    @else
      <form action="{{ route('tweets.like', $tweet) }}" method="POST">
        @csrf
        <button type="submit" class="text-blue-500 hover:text-blue-700">{{ __('ui.tweets.like') }} {{ $tweet->liked->count() }}</button>
      </form>
    @endif

  </div>
</div>
