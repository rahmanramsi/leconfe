<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

class FilterButton extends Component
{
    public string $filterName;

    public array $filterOptions = [];

    public array $filterOutputOptions = [];

    public array $multipleFilterValue = [];

    public string $singleFilterValue = "";

    public bool $multiple;

    public string $search = "";


    protected $listeners = [
        'clearAllFilter' => 'clearFilter',
    ];

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
        $this->search = "";

        $this->filterChanged();
    }


    public function render()
    {
        $filterOptions = collect($this->filterOptions ?? []);

        $this->filterOutputOptions = $filterOptions->filter(function (mixed $value) {
            $stringValue = is_string($value) ? $value : (string) $value;

            if ($this->search === "") {
                return true;
            }

            if (Str::contains(Str::lower($stringValue), Str::lower($this->search))) {
                return true;
            }

            return false;
        })->toArray();

        return view('livewire.filter-button', [
            'isMultiple' => $this->multiple ?? false,
            'filterName' => $this->filterName,
            'filterOutputOptions' => $this->filterOutputOptions,
            // value
            'multipleFilterValue' => $this->multipleFilterValue ?? [],
            'singleFilterValue' => $this->singleFilterValue ?? "",
        ]);
    }
}
