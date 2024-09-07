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
                    <input type="search" class="grow" placeholder="{{ __('general.search') }}" wire:model.live="search" />
                    <x-heroicon-m-magnifying-glass class="h-3 w-3 opacity-70" />
                </label>
            @endif

            <button class="mb-2 btn btn-xs btn-outline border-gray-300 w-full" wire:click="clearFilter" wire:loading.attr="disabled">
                Reset
            </button>
        </li>

        @if ($isMultiple)
            @foreach ($filterOutputOptions as $filterKey => $filterValue)
                <li>
                    <label class="py-1.5 label cursor-pointer hover:bg-neutral-content hover:!text-white transition-colors rounded">
                        <span class="label-text px-2">{{ $filterValue }}</span>
                        <input type="checkbox" class="checkbox checkbox-xs mx-1.5" value="{{ $filterValue }}" wire:model.live="multipleFilterValue" wire:change="filterChanged()" />
                    </label>
                </li>
            @endforeach
        @else
            @foreach ($filterOutputOptions as $filterKey => $filterValue)
                <li>
                    <label class="py-1.5 label cursor-pointer hover:bg-neutral-content hover:!text-white transition-colors rounded">
                        <span class="label-text px-2">{{ $filterValue }}</span>
                        <input type="radio" class="radio radio-xs mx-1.5" value="{{ $filterValue }}" wire:model.live="singleFilterValue" wire:change="filterChanged" />
                    </label>
                </li>
            @endforeach
        @endif

        @if (empty($filterOutputOptions))
            <li>
                <p class="text-center text-xs">Option are empty</p>
            </li>
        @endif
    </ul>
</div>
