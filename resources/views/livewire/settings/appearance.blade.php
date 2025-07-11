<?php

use Livewire\Volt\Component;

new class extends Component
{
    public function themes(): array
    {
        return [
            ['name' => 'Havelock Blue', 'value' => 'havelock-blue'],
            ['name' => 'Earthen & Arcane', 'value' => 'earthen-arcane'],
            ['name' => 'Heroic & Fiery', 'value' => 'heroic-fiery'],
            ['name' => 'Mystic & Verdant', 'value' => 'mystic-verdant'],
        ];
    }
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Mode') }}</h3>
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" class="mt-2">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Color Theme') }}</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Select your preferred color accent for the application.') }}
                </p>
                <div x-data class="mt-2">
                    <flux:radio.group variant="segmented" x-model="$store.appTheme.currentTheme">
                        @foreach ($this->themes() as $theme)
                            <flux:radio value="{{ $theme['value'] }}">{{ $theme['name'] }}</flux:radio>
                        @endforeach
                    </flux:radio.group>
                </div>
            </div>
        </div>
    </x-settings.layout>
</div>
