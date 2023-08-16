@props([
    'items' => [],
    'level' => 1,
])

<div
    {{ $attributes->twMerge(['flex flex-col divide-y p-1 mt-1 min-w-[12rem] bg-white rounded-md shadow-md']) }}>
    @foreach ($items as $key => $item)
        {{-- Limit level dropdown to only 1 level --}}
        @if (!empty($item['children']) && $level == 1)
            <div class="relative group">
                <div
                    class="relative flex cursor-default select-none hover:bg-neutral-100 items-center py-1.5 px-4 pr-2 text-sm outline-none transition-colors data-[disabled]:pointer-events-none data-[disabled]:opacity-50">
                    <span>{{ $item['label'] }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="w-4 h-4 ml-auto">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div data-submenu
                    class="absolute top-0 right-0 invisible mr-1 duration-200 ease-out translate-x-full opacity-0 group-hover:mr-0 group-hover:visible group-hover:opacity-100">
                    <x-website::navigation.dropdown.items :items="$item['children']" :level="$level + 1"
                        class="z-50 min-w-[8rem] overflow-hidden" />
                </div>
            </div>
            @continue
        @endif

        @switch($item['type'])
            @case('external-link')
                <x-website::navigation.dropdown.item.link class="w-full text-left p-0" :label="$item['label']" :url="$item['data']['url']" />
            @break

            @default
                <x-website::navigation.dropdown.item.link class="w-full text-left p-0" :label="$item['label']" />
            @break
        @endswitch
    @endforeach
</div>
