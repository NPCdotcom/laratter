<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('ui.settings.language_heading')" :subheading="__('ui.settings.language_subheading')">
        <form method="POST" action="{{ route('locale.update') }}" class="space-y-4">
            @csrf
            <div>
                <label for="settings-locale" class="mb-2 block text-sm font-medium">
                    {{ __('ui.settings.language_label') }}
                </label>
                <select
                    id="settings-locale"
                    name="locale"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                >
                    @foreach (config('locales.supported') as $code => $label)
                        <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('locale')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <flux:button variant="primary" type="submit">{{ __('ui.common.save') }}</flux:button>
        </form>
    </x-settings.layout>
</div>
