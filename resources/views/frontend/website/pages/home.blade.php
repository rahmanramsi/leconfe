<x-website::layouts.main>
    <div class="space-y-5">
        @if ($site->getMeta('about'))
            <div class="description user-content">
                {{ new Illuminate\Support\HtmlString($site->getMeta('about')) }}
            </div>
        @endif

        <div class="space-y-4 conferences">
            <div x-data="{filter: false}">
                <div class="flex">
                    <x-website::heading-title title="Conference List" class="grow"/>
                    <div class="tooltip tooltip-bottom" data-tip="{{ __('general.filter') }}">
                        <label class="px-1 py-0.5 flex-none swap swap-rotate">
                            <input type="checkbox" @click="filter = !filter" />
                            <x-heroicon-m-funnel class="swap-off h-5 w-5 fill-current" />
                            <x-heroicon-m-x-mark class="swap-on h-5 w-5 fill-current" />
                        </label>
                    </div>
                </div>
                <div class="mt-2 px-5 py-4 bg-gray-100 rounded" x-show="filter" x-transition>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 gap-2">
                            <label class="input input-sm input-bordered bg-white flex items-center gap-2">
                                <input type="text" class="grow" placeholder="{{ __('general.search') }}" wire:model.live="search" />
                                <x-heroicon-m-magnifying-glass class="h-4 w-4 opacity-70" />
                            </label>
                        </div>

                        <hr class="col-span-12 gap-2">

                        <div class="col-span-3 gap-2">
                            <h1 class="font-semibold text-base">{{ __('general.scope') }}</h1>
                            <div class="form-control">
                                <label class="label cursor-pointer w-fit">
                                    <input type="checkbox" class="checkbox checkbox-xs" value="{{ App\Models\Conference::SCOPE_INTERNATIONAL }}" wire:model.live="scope" />
                                    <span class="label-text px-2">{{ __('general.international') }}</span>
                                </label>
                                <label class="label cursor-pointer w-fit">
                                    <input type="checkbox" class="checkbox checkbox-xs" value="{{ App\Models\Conference::SCOPE_NATIONAL }}" wire:model.live="scope" />
                                    <span class="label-text px-2">{{ __('general.national') }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-span-3 gap-2">
                            <h1 class="font-semibold text-base">{{ __('general.state') }}</h1>
                            <div class="form-control">
                                <label class="label cursor-pointer w-fit">
                                    <input type="checkbox" class="checkbox checkbox-xs" value="active" wire:model.live="state" />
                                    <span class="label-text px-2">{{ __('general.active') }}</span>
                                </label>
                                <label class="label cursor-pointer w-fit">
                                    <input type="checkbox" class="checkbox checkbox-xs" value="over" wire:model.live="state" />
                                    <span class="label-text px-2">{{ __('general.over') }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-span-3 gap-2">
                            <h1 class="font-semibold text-base">{{ __('general.topic') }}</h1>
                            <div class="form-control">
                                <label class="label cursor-pointer w-fit">
                                    <input type="checkbox" class="checkbox checkbox-xs" value="1" wire:model.live="topic" />
                                    <span class="label-text px-2">topic1</span>
                                </label>
                                <label class="label cursor-pointer w-fit">
                                    <input type="checkbox" class="checkbox checkbox-xs" value="2" wire:model.live="topic" />
                                    <span class="label-text px-2">topic2</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-span-3 gap-2">
                            <h1 class="font-semibold text-base">{{ __('general.coordinator') }}</h1>
                            <div class="form-control">
                                <label class="label cursor-pointer w-fit">
                                    <input type="checkbox" class="checkbox checkbox-xs" value="1" wire:model.live="coordinator" />
                                    <span class="label-text px-2">coordinator1</span>
                                </label>
                                <label class="label cursor-pointer w-fit">
                                    <input type="checkbox" class="checkbox checkbox-xs" value="2" wire:model.live="coordinator" />
                                    <span class="label-text px-2">coordinator2</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
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
