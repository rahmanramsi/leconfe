<x-filament-panels::page>
    <form wire:submit='submit' class="space-y-4">
        {{ $this->form }}
        <div class="flex items-center gap-2">
            <x-filament::button type="submit" icon="iconpark-save-o">
                {{ __('general.submit') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>