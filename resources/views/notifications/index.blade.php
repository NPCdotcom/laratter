<x-layouts.app :title="__('ui.notifications.title')">
  <div class="p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h2 class="font-semibold text-xl">{{ __('ui.notifications.title') }}</h2>
      <form method="POST" action="{{ route('notifications.read') }}">
        @csrf
        <button type="submit" class="text-sm text-blue-500 hover:text-blue-700">
          {{ __('ui.notifications.mark_all_read') }}
        </button>
      </form>
    </div>

    <div class="mb-6 flex gap-4 border-b border-gray-200 dark:border-gray-600">
      <a
        href="{{ route('notifications.index', ['tab' => 'general']) }}"
        class="pb-2 text-sm font-medium {{ $tab === 'general' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
      >
        {{ __('ui.notifications.tab_general') }}
        @if ($unreadGeneral > 0)
          <span class="ms-1 rounded-full bg-red-500 px-1.5 py-0.5 text-xs text-white">{{ $unreadGeneral }}</span>
        @endif
      </a>
      <a
        href="{{ route('notifications.index', ['tab' => 'follow']) }}"
        class="pb-2 text-sm font-medium {{ $tab === 'follow' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
      >
        {{ __('ui.notifications.tab_follow') }}
        @if ($unreadFollow > 0)
          <span class="ms-1 rounded-full bg-red-500 px-1.5 py-0.5 text-xs text-white">{{ $unreadFollow }}</span>
        @endif
      </a>
    </div>

    @forelse ($notifications as $notification)
      @php
        $data = $notification->data;
        $actorId = $data['actor_id'] ?? null;
        $actorName = $data['actor_name'] ?? '';
        $message = __($data['message_key'] ?? 'ui.notifications.title', [
          'name' => $actorName,
          'tweet' => $data['tweet_excerpt'] ?? '',
        ]);
      @endphp
      <div class="mb-3 rounded-lg p-4 {{ $notification->read_at ? 'bg-gray-50 dark:bg-gray-800' : 'bg-blue-50 dark:bg-gray-700' }}">
        <p class="text-sm">{{ $message }}</p>
        <p class="mt-1 text-xs text-gray-500">{{ $notification->created_at->format('Y-m-d H:i') }}</p>

        <div class="mt-2 flex flex-wrap gap-3 text-sm">
          @if ($actorId)
            <a href="{{ route('profile.show', $actorId) }}" class="text-blue-500 hover:text-blue-700">
              {{ __('ui.notifications.view_profile') }}
            </a>
          @endif

          @if (! empty($data['url']))
            <a href="{{ $data['url'] }}" class="text-blue-500 hover:text-blue-700">
              {{ __('ui.common.view_details') }}
            </a>
          @endif

          @if (($data['type'] ?? null) === 'follow_requested' && ! empty($data['actionable']) && $actorId)
            <form action="{{ route('follow.accept', $actorId) }}" method="POST" class="inline">
              @csrf
              <button type="submit" class="text-emerald-600 hover:text-emerald-700">{{ __('ui.notifications.accept') }}</button>
            </form>
            <form action="{{ route('follow.reject', $actorId) }}" method="POST" class="inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-red-500 hover:text-red-700">{{ __('ui.notifications.reject') }}</button>
            </form>
          @endif
        </div>
      </div>
    @empty
      <p class="text-sm text-gray-500">{{ __('ui.notifications.empty') }}</p>
    @endforelse

    <div class="mt-4">
      {{ $notifications->links() }}
    </div>
  </div>
</x-layouts.app>
