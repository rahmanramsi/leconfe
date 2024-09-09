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
        'clearFilter' => 'clearFilter',
    ];

    public function mount(string $name, array $options, bool $is_multiple = false): void
    {
        $this->filterName = $name;
        $this->filterOptions = $options;
        $this->multiple = $is_multiple;
    }

    public function filterChanged(): void
    {
        $this->dispatch('changeFilter', [
            $this->filterName => $this->multiple ? $this->multipleFilterValue : $this->singleFilterValue,
        ]);
    }

    public function clearFilter(?string $specifiedFilter = null): void
    {
        if($specifiedFilter !== $this->filterName && $specifiedFilter) {
            return;
        }

        $this->multipleFilterValue = [];
        $this->singleFilterValue = "";

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
        ]);
    }
}
