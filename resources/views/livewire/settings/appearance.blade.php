<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('ui.settings.appearance_heading')" :subheading="__('ui.settings.appearance_subheading')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('ui.settings.appearance_light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('ui.settings.appearance_dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('ui.settings.appearance_system') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</div>
