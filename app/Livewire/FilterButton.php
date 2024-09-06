<?php

namespace App\Livewire;

use Illuminate\Support\Arr;
use Livewire\Component;
use Illuminate\Support\Str;

class FilterButton extends Component
{
    public string $filterName;

    public ?array $filterOptions = null;

    public ?array $filterOutputOptions = null;

    public array $multipleFilterValue = [];

    public string $singleFilterValue = "";

    public bool $multiple;

    public string $search = "";

    public function mount(string $filterName, array $filterOptions, bool $multiple = false): void
    {
        $this->filterName = $filterName;
        $this->filterOptions = $filterOptions;
        $this->multiple = $multiple;
    }

    public function filterChanged(): void
    {
        $this->dispatch('changeFilter', [
            $this->filterName => $this->multiple ? $this->multipleFilterValue : $this->singleFilterValue,
        ]);
    }

    public function clearFilter(): void
    {
        $this->multipleFilterValue = [];
        $this->singleFilterValue = "";

        $this->filterChanged();
    }

    public function searchFilter(string $search): void
    {
        $outputOptions = Arr::mapWithKeys($this->filterOptions, function ($value, $key) {
            $stringValue = is_string($value) ? $value : (string) $value;
            if(str_contains($stringValue, $this->search) || $this->search === "") {
                return [$key => $value];
            }
        });

        $this->filterOutputOptions = $outputOptions;
    }

    public function render()
    {
        return view('livewire.filter-button', [
            'isMultiple' => $this->multiple ?? false,
            'filterName' => $this->filterName,
            'filterOptions' => $this->filterOutputOptions,
            // value
            'multipleFilterValue' => $this->multipleFilterValue ?? [],
            'singleFilterValue' => $this->singleFilterValue ?? "",
        ]);
    }
}
