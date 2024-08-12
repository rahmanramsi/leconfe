<x-website::layouts.main>
    <div class="space-y-5">
        @if ($site->getMeta('about'))
            <div class="description user-content">
                {{ new Illuminate\Support\HtmlString($site->getMeta('about')) }}
            </div>
        @endif

        <div class="space-y-4 conferences">
            <x-website::heading-title title="Conference List" />
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
                                    <h2 class="">
                                        <a href="{{ $conference->getHomeUrl() }}"
                                            class="font-bold conference-name link link-primary link-hover">{{ $conference->name }}</a>
                                    </h2>

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
