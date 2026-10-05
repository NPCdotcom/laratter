<a href="{{ route('tweets.edit', $tweet) }}" class="text-blue-500 hover:text-blue-700">{{ __('ui.common.edit') }}</a>
<form action="{{ route('tweets.destroy', $tweet) }}" method="POST" class="inline" onsubmit="return confirm(@js(__('ui.common.confirm_delete')));">
  @csrf
  @method('DELETE')
  <button type="submit" class="text-red-500 hover:text-red-700">{{ __('ui.common.delete') }}</button>
</form>
