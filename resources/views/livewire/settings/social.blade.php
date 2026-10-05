<?php

use Livewire\Volt\Component;

new class extends Component {
    public bool $auto_accept_follows = true;

    public function mount(): void
    {
        $this->auto_accept_follows = (bool) auth()->user()->auto_accept_follows;
    }

    public function save(): void
    {
        auth()->user()->update([
            'auto_accept_follows' => $this->auto_accept_follows,
        ]);

        $this->dispatch('social-updated');
    }
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('ui.settings.social_heading')" :subheading="__('ui.settings.social_subheading')">
        <form wire:submit="save" class="space-y-6">
            <label class="flex items-start gap-3">
                <input
                    type="checkbox"
                    wire:model="auto_accept_follows"
                    class="mt-1 rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800"
                >
                <span>
                    <span class="block text-sm font-medium">{{ __('ui.settings.auto_accept_follows') }}</span>
                    <span class="mt-1 block text-sm text-zinc-500">{{ __('ui.settings.auto_accept_follows_help') }}</span>
                </span>
            </label>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('ui.common.save') }}</flux:button>
                <x-action-message class="me-3" on="social-updated">
                    {{ __('ui.settings.saved') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</div>
