<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-m-building-office-2" icon-color="info">
        <x-slot name="heading">Venue</x-slot>
           @foreach ( $venue as $venues )
        <div class="flex gap-3 p-2">
               
            <div class="flex border">
              <img src="{{ $venues ->getFirstMedia('venue_photos') ? $venues ->getFirstMedia('venue_photos')->getAvailableUrl(['small', 'thumb', 'thumb-xl']) : '' }}" alt="">

            </div>

            <div class="flex flex-col">
                <div class="s">
                <x-filament::link size="sm" color='info' class="font-thin">
                  {{ $venues->name }}
                </x-filament::link>
                <p class="text-gray-500 text-xs">{{ $venues->location }}</p>
                </div>
        </div>

        </div>
           @endforeach

    </x-filament::section>
</x-filament-widgets::widget>
