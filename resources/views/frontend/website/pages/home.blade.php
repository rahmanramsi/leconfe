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

                <div class="col-span-4 sm:col-span-2 dropdown h-fit">
                    <button tabindex="0" role="button" class="btn btn-sm btn-outline border-gray-300 w-full">
                        {{ __('general.scope') }} {{ $this->scope ? "(Selected)" : null }}
                        <x-heroicon-o-chevron-down class="h-4 w-4" />
                    </button>
                    <ul tabindex="0" class="mt-2 p-2 w-full dropdown-content form-control bg-white border rounded z-[1] shadow-xl">
                        <li>
                            <button class="mb-2 btn btn-xs btn-outline border-gray-300 w-full" wire:click="clearScope" wire:loading.attr="disabled">
                                Clear
                            </button>
                        </li>
                        <li>
                            <label class="py-1.5 label cursor-pointer">
                                <span class="label-text px-2 !text-center">{{ __('general.international') }}</span>
                                <input type="radio" class="radio radio-xs" value="{{ App\Models\Conference::SCOPE_INTERNATIONAL }}" wire:model.live="scope" />
                            </label>
                        </li>
                        <li>
                            <label class="py-1.5 label cursor-pointer">
                                <span class="label-text px-2 !text-center">{{ __('general.national') }}</span>
                                <input type="radio" class="radio radio-xs" value="{{ App\Models\Conference::SCOPE_NATIONAL }}" wire:model.live="scope" />
                            </label>
                        </li>
                    </ul>
                </div>

                <div class="col-span-4 sm:col-span-2 dropdown h-fit">
                    <button tabindex="0" role="button" class="btn btn-sm btn-outline border-gray-300 w-full">
                        {{ __('general.state') }} {{ $this->state ? "(Selected)" : null }}
                        <x-heroicon-o-chevron-down class="h-4 w-4" />
                    </button>
                    <ul tabindex="0" class="mt-2 p-2 w-full dropdown-content form-control bg-white border rounded z-[1] shadow-xl">
                        <li>
                            <button class="mb-2 btn btn-xs btn-outline border-gray-300 w-full" wire:click="clearState" wire:loading.attr="disabled">
                                Clear
                            </button>
                        </li>
                        <li>
                            <label class="py-1.5 label cursor-pointer">
                                <span class="label-text px-2">{{ __('general.active') }}</span>
                                <input type="radio" class="radio radio-xs" value="active" wire:model.live="state" />
                            </label>
                        </li>
                        <li>
                            <label class="py-1.5 label cursor-pointer">
                                <span class="label-text px-2">{{ __('general.over') }}</span>
                                <input type="radio" class="radio radio-xs" value="over" wire:model.live="state" />
                            </label>
                        </li>
                    </ul>
                </div>
                
                <div class="col-span-4 sm:col-span-2 dropdown h-fit">
                    <button tabindex="0" role="button" class="btn btn-sm btn-outline border-gray-300 w-full">
                        {{ __('general.topic') }} {{ count($this->topic) > 0 ? "(" . count($this->topic) . ")" : null }}
                        <x-heroicon-o-chevron-down class="h-4 w-4" />
                    </button>
                    <ul tabindex="0" class="mt-2 p-2 w-full grid dropdown-content bg-white border rounded z-[1] shadow-xl overflow-y-auto max-h-[50vh]">
                        <li>
                            <label class="mb-2 input input-xs input-bordered !outline-none bg-white flex items-center">
                                <input type="text" class="grow" placeholder="{{ __('general.search') }}" wire:model.live.debounce="topicSearch" />
                                <x-heroicon-m-magnifying-glass class="h-3 w-3 opacity-70" />
                            </label>
                        </li>
                        @foreach ($topics as $topic)
                            <li>
                                <label class="py-1.5 label cursor-pointer">
                                    <span class="label-text px-2">{{ $topic->name }}</span>
                                    <input type="checkbox" class="checkbox checkbox-xs" value="{{ $topic->id }}" wire:model.live="topic" />
                                </label>
                            </li>
                        @endforeach
                        @if ($topics->isEmpty())
                            <li>
                                <p class="text-center text-xs">Option are empty</p>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="col-span-4 sm:col-span-2 dropdown h-fit">
                    <button tabindex="0" role="button" class="btn btn-sm btn-outline border-gray-300 w-full">
                        {{ __('general.coordinator') }} {{ count($this->coordinator) > 0 ? "(" . count($this->coordinator) . ")" : null }}
                        <x-heroicon-o-chevron-down class="h-4 w-4" />
                    </button>
                    <ul tabindex="0" class="mt-2 p-2 w-full grid dropdown-content bg-white border rounded z-[1] shadow-xl overflow-y-auto max-h-[50vh]">
                        <li>
                            <label class="mb-2 input input-xs input-bordered !outline-none bg-white flex items-center">
                                <input type="text" class="grow" placeholder="{{ __('general.search') }}" wire:model.live.debounce="coordinatorSearch" />
                                <x-heroicon-m-magnifying-glass class="h-3 w-3 opacity-70" />
                            </label>
                        </li>
                        @foreach ($scheduledConferencesWithCoordinators as $scheduledConference)
                            <li>
                                <label class="py-1.5 label cursor-pointer">
                                    <span class="label-text px-2">{{ $scheduledConference->getMeta('coordinator') }}</span>
                                    <input type="checkbox" class="checkbox checkbox-xs" value="{{ $scheduledConference->id }}" wire:model.live="coordinator" />
                                </label>
                            </li>
                        @endforeach
                        @if ($scheduledConferencesWithCoordinators->isEmpty())
                            <li>
                                <p class="text-center text-xs">Option are empty</p>
                            </li>
                        @endif
                    </ul>
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
