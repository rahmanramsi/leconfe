<x-website::layouts.main>
    <div class="space-y-5">
        @if ($site->getMeta('about'))
            <div class="description user-content">
                {{ new Illuminate\Support\HtmlString($site->getMeta('about')) }}
            </div>
        @endif

        <div class="space-y-4 conferences">

            <x-website::heading-title title="Conference List" class="grow"/>

            <div class="mt-6 mb-6 grid grid-cols-8 gap-2">
                <div class="col-span-6 gap-2">
                    <label class="input input-sm input-bordered !outline-none bg-white flex items-center gap-2">
                        <input type="text" class="grow" placeholder="{{ __('general.search') }}" wire:model.live.debounce="search" />
                        <x-heroicon-m-magnifying-glass class="h-4 w-4 opacity-70" />
                    </label>
                </div>

                <button class="col-span-2 btn btn-sm btn-primary w-full" wire:click="clearFilter" wire:loading.attr="disabled">
                    {{ __('general.clear') }}
                </button>

                <div class="col-span-4 sm:col-span-2">
                    @livewire(\App\Livewire\FilterButton::class, [
                        'name' => 'scope',
                        'options' => [
                            App\Models\Conference::SCOPE_INTERNATIONAL => __('general.international'),
                            App\Models\Conference::SCOPE_NATIONAL => __('general.national'),
                        ],
                        'is_multiple' => false,
                    ])
                </div>

                <div class="col-span-4 sm:col-span-2">
                    @livewire(\App\Livewire\FilterButton::class, [
                        'name' => 'state',
                        'options' => [
                            'active' => __('general.active'),
                            'over' => __('general.over'),
                        ],
                        'is_multiple' => false,
                    ])
                </div>


                <div class="col-span-4 sm:col-span-2">
                    @livewire(\App\Livewire\FilterButton::class, [
                        'name' => 'topic',
                        'options' => $topics->pluck('name', 'id')->toArray(),
                        'is_multiple' => true,
                    ])
                </div>

                <div class="col-span-4 sm:col-span-2">
                    @livewire(\App\Livewire\FilterButton::class, [
                        'name' => 'coordinator',
                        'options' => $scheduledConferencesWithCoordinators->mapWithKeys(function ($scheduledConference) {
                            return [$scheduledConference->id => $scheduledConference->getMeta('coordinator')];
                        })->toArray(),
                        'is_multiple' => true,
                    ])
                </div>
            </div>
            <hr class="!my-6">
            <div class="space-y-4 conference-current">
                @if ($conferences->isNotEmpty())
                    <div class="grid gap-6 xl:grid-cols-2">
                        @foreach ($conferences as $conference)
                            <div class="gap-4 conference sm:flex">
                                @if ($conference->hasThumbnail())
                                    <div class="cover max-w-40">
                                        <img src="{{ $conference->getThumbnailUrl() }}" alt="{{ $conference->name }}">
                                    </div>
                                @endif
                                <div class="flex-1 space-y-2 information">
                                    <h3>
                                        <a href="{{ $conference->getHomeUrl() }}"
                                            class="font-bold conference-name link link-primary link-hover">{{ $conference->name }}</a>
                                    </h3>

                                    @if ($conference->getMeta('summary'))
                                        <div class="conference-summary user-content">
                                            {!! $conference->getMeta('summary') !!}
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-2 text-sm">
                                        <a href="{{ $conference->getHomeUrl() }}" class="link link-primary">{{ __('general.view_conference') }}</a>
                                        @if($conference->currentScheduledConference)
                                            <a href="{{ $conference->currentScheduledConference->getHomeUrl() }}" class="link link-primary">{{ __('general.view_current_event') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="my-12 text-center">
                        <p class="text-lg font-bold">{{ __('general.there_are_no_conferences_taking_place_at_this_time') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-website::layouts.main>
