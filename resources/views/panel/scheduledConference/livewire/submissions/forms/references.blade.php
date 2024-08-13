<x-filament::section heading="{{ __('general.references') }}">
    <form wire:submit='submit' class="space-y-4">
        {{ $this->form }}
        @can('editing', $submission)
            <x-filament::button type="submit" icon="iconpark-save-o">
                {{ __('general.submit') }}
            </x-filament::button>
        @endcan
    </form>
</x-filament::section>
