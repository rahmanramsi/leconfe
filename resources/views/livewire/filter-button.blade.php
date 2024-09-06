<div class="dropdown h-fit w-full">
    <button tabindex="0" role="button" class="btn btn-sm btn-outline border-gray-300 w-full">
        @if ($isMultiple)
            {{ Str::headline($filterName) }} {{ !empty($multipleFilterValue) ? "(" . count($multipleFilterValue) . ")" : null }}
        @else
            {{ Str::headline($filterName) }} {{ $singleFilterValue ? "(Selected)" : null }}
        @endif
        <x-heroicon-o-chevron-down class="h-4 w-4" />
    </button>

    <ul tabindex="0" class="mt-2 p-2 w-full grid dropdown-content bg-white border rounded z-[1] shadow-xl overflow-y-auto max-h-[50vh]">
        <li>
            @if ($isMultiple)
                <label class="mb-2 input input-xs input-bordered !outline-none bg-white flex items-center">
                    <input type="text" class="grow" placeholder="{{ __('general.search') }}" wire:model.live.debounce="search" wire:change="searchFilter('asdasd')" />
                    <x-heroicon-m-magnifying-glass class="h-3 w-3 opacity-70" />
                </label>
            @else
                <button class="mb-2 btn btn-xs btn-outline border-gray-300 w-full" wire:click="clearFilter" wire:loading.attr="disabled">
                    Clear
                </button>
            @endif
        </li>

        @if ($isMultiple)
            @foreach ($filterOptions as $filterKey => $filterValue)
                <li>
                    <label class="py-1.5 label cursor-pointer">
                        <span class="label-text px-2">{{ $filterValue }}</span>
                        <input type="checkbox" class="checkbox checkbox-xs" value="{{ $filterKey }}" wire:model.live="multipleFilterValue" wire:change="filterChanged" />
                    </label>
                </li>
            @endforeach
        @else
            @foreach ($filterOptions as $filterKey => $filterValue)
                <li>
                    <label class="py-1.5 label cursor-pointer">
                        <span class="label-text px-2">{{ $filterValue }}</span>
                        <input type="radio" class="radio radio-xs" value="{{ $filterKey }}" wire:model.live="singleFilterValue" wire:change="filterChanged" />
                    </label>
                </li>
            @endforeach
        @endif

        @if (empty($filterOptions))
            <li>
                <p class="text-center text-xs">Option are empty</p>
            </li>
        @endif
    </ul>
</div>
