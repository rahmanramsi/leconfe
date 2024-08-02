<x-filament::dropdown.list>
    @foreach ($languages as $locale => $language)
        <x-filament::dropdown.list.item :icon="$locale === app()->getLocale() ? 'heroicon-o-check' : ''" tag="button" wire:click="switchLanguage('{{ $locale }}')">
            {{ $language }}
        </x-filament::dropdown.list.item>
    @endforeach
</x-filament::dropdown.list>
