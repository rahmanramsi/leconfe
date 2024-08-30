<section class="citation">
    <h2 class="pb-1 mb-3 text-base font-medium border-b border-b-slate-200">
        {{ __('general.how_to_cite') }}
    </h2>
    <div class="mt-4 content text-slate-800 space-y-2">
        <div id="citationOutput" class="user-content break-words" wire:loading.class="opacity-50" wire:target="updateCitationStyle">
            {!! $citation !!}
        </div>
        <div
            wire:ignore
            x-data="{ open: false }" 
            x-on:click.away="open = false"
            {{-- x-on:mouseleave="open = false" --}}
            >
            <button 
                x-on:click="open = !open"
                x-ref="button"
                class="btn btn-primary btn-sm no-animation text-sm rounded-lg inline-flex items-center justify-center px-4 transition-colors focus:outline-none disabled:opacity-50 disabled:pointer-events-none group w-max gap-0 ease-out duration-300">
                {{ __('general.more_citation_formats') }}
                <x-heroicon-m-chevron-down class="transition relative top-[1px] ms-1 h-3 w-3" />
            </button>
            @teleport('body')
                <div 
                    x-show="open"
                    x-transition
                    x-anchor.offset.10.top-start="$refs.button"
                    x-cloak
                    class="navbar-dropdown-content text-gray-800"
                    >
                    <div class="flex flex-col divide-y mt-1 min-w-[12rem] bg-white rounded-md shadow-md border">
                        @foreach ($citationStyles as $styles)
                            <button
                                wire:click='updateCitationStyle("{{ $styles['id'] }}")'
                                x-on:click="open = false"
                                class="first:rounded-t-md last:rounded-b-md relative flex hover:bg-base-content/10 items-center py-2 px-4 pr-6 text-sm outline-none transition-colors gap-4 w-full">
                                {{ $styles['title'] }}
                            </button>
                        @endforeach
                        @if(!empty($citationDownloads))
                            <div class="font-medium py-2 px-4 text-gray-700">{{ __('general.download_citation') }}</div>
                            @foreach ($citationDownloads as $styles)
                                <button
                                    wire:click='downloadCitation("{{ $styles['id'] }}")'
                                    x-on:click="open = false"
                                    class="first:rounded-t-md last:rounded-b-md relative flex hover:bg-base-content/10 items-center py-2 px-4 pr-6 text-sm outline-none transition-colors gap-4 w-full">
                                    {{ $styles['title'] }}
                                </button>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endteleport
        </div>
    </div>
</section>