<x-filament-panels::page x-on:show-guidelines="$dispatch('open-modal', {'id': 'guidelines'})"  x-data="
  {  
    autoShowGuidelinesEnable() {
        return localStorage.getItem('autoShowGuidelines') != 0;
    },
    toggleAutoShowGuidelines() {
        localStorage.setItem('autoShowGuidelines', this.autoShowGuidelinesEnable() ? 0 : 1);
    },
    init() {
        if(this.autoShowGuidelinesEnable()) {
            $nextTick(() => {
                $dispatch('open-modal', {'id': 'guidelines'})
            });
        }
    }
}
    ">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-8 space-y-4">
            @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\ReviewerAssignedFiles::class, ['record' => $review])
            {{ $this->reviewForm }}
            @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\ReviewerFiles::class, ['record' => $review])
        </div>
        <div class="col-span-4 space-y-4 self-start sticky top-20">
            {{ $this->infolist }}
            {{ $this->recommendationForm }}
            {{ $this->reviewAction() }}
        </div>
    </div>
    <x-filament::modal id="guidelines" width="2xl" :close-by-clicking-away="false" >
        <x-slot name="heading">
            <h1 class="text-xl font-bold">
                Review Guidlines & Competing Interests
            </h1>
        </x-slot>
        <x-slot name="description">
            Please read the following guidelines and competing interests before submitting your review.
        </x-slot>
        <div class="flex flex-col space-y-4">
            <div>
                <h2 class="text-lg font-bold">
                    Review Guidelines
                </h2>
                {!! $currentScheduledConference->getMeta('review_guidelines') !!}
            </div>
            <div>
                <h2 class="text-lg font-bold">
                    Competing Interests
                </h2>
                {!! $currentScheduledConference->getMeta('competing_interests') !!}
            </div>

            <div class="flex items-center mb-4">
                <input id="default-checkbox" type="checkbox" value="" class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" :checked="!autoShowGuidelinesEnable()" x-on:change="toggleAutoShowGuidelines()">
                <label for="default-checkbox" class="ms-2 text-sm text-gray-900 dark:text-gray-300">
                    Understood guidelines and interests. Don't show this again
                </label>
            </div>

            <x-slot name="footerActions">
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', {'id': 'guidelines'})">
                    Close
                </x-filament::button>
            </x-slot>
        </div>
    </x-filament::modal>
</x-filament-panels::page>
