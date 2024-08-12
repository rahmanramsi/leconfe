<div class="space-y-6">
    <div class="p-6 bg-white border border-gray-200 filament-forms-card-component dark:bg-gray-900 rounded-xl dark:border-gray-800">
        <div class="grid grid-cols-1 gap-6 filament-forms-component-container">
            <div class="col-span-full">
                <div id="upload-files" class="grid grid-cols-1 filament-forms-section-component md:grid-cols-2">
                    <div
                        class="filament-forms-section-header-wrapper flex rtl:space-x-reverse overflow-hidden min-h-[56px] pr-6 pb-4">
                        <div class="flex-1 space-y-1 filament-forms-section-header">
                            <h3 class="flex flex-row items-center text-xl font-bold tracking-tight pointer-events-none">
                                {{ __('general.upload_files') }}
                            </h3>

                            <p class="text-base text-gray-500">
                                {{ __('general.include_any_necessary_files') }}
                            </p>
                        </div>
                    </div>
                        @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\Files\AbstractFiles::class, ['submission' => $record])
                </div>
            </div>
        </div>
    </div>
    <div class="flex items-center justify-between">
        <div>
            <x-filament::button icon="heroicon-o-chevron-left" x-show="! isFirstStep()" x-cloak x-on:click="previousStep"
                color="gray" size="sm">
              {{__('general.previous')}}
            </x-filament::button>
        </div>
        <div>
            {{ $this->nextStep() }}
        </div>
    </div>
</div>
