<x-website::layouts.main>
    @if ($conference->hasMedia('cover'))
        <div class="conference-cover">
            <img class="h-full" src="{{ $conference->getFirstMedia('cover')->getAvailableUrl(['thumb', 'thumb-xl']) }}"
                alt="{{ $conference->title }}" />
        </div>
    @endif
    @if ($conference->getMeta('about'))
        <div class="conference-about user-content mb-4">
            {!! $conference->getMeta('about') !!}
        </div>
    @endif
    @if($nextScheduledConference || $pastScheduledConferences->isNotEmpty())
    <div class="scheduled-conferences space-y-6">
        @if($nextScheduledConference)
            <div class="next-scheduled-conference">
                <x-website::heading-title title="Next Conference" class="mb-5" />
                <div class="next-scheduled-conference sm:flex gap-4">
                    @if ($nextScheduledConference->hasThumbnail())
                        <div class="next-scheduled-conference-cover max-w-40">
                            <img src="{{ $nextScheduledConference->getThumbnailUrl() }}" alt="{{ $nextScheduledConference->title }}">
                        </div>
                    @endif
                    <div class="information flex-1 space-y-1">
                        <h3 class="next-scheduled-conference-title">
                            <a href="{{ $nextScheduledConference->getHomeUrl() }}"
                                class="link link-primary link-hover font-medium">{{ $nextScheduledConference->title }}</a>
                        </h3>
                        <div class="next-scheduled-conference-date text-sm text-gray-700">
                            @if($nextScheduledConference->date_start)
                                {{ $nextScheduledConference->date_start->format(Setting::get('format_date')) }}
                            @endif
                            @if($nextScheduledConference->date_end)
                                - {{ $nextScheduledConference->date_end->format(Setting::get('format_date')) }}
                            @endif
                        </div>

                        @if ($nextScheduledConference->getMeta('summary'))
                            <div class="scheduled-conference-summary user-content">
                                {!! $nextScheduledConference->getMeta('summary') !!}
                            </div>
                        @endif

                        <a href="{{ $nextScheduledConference->getHomeUrl() }}" class="link link-primary text-sm">View Current Event</a>
                    </div>
                </div>
            </div>
        @endif
        @if($pastScheduledConferences->isNotEmpty())
            <div class="past-scheduled-conferences">
                <x-website::heading-title title="Past Conferences" class="mb-5"/>
                <div class="space-y-4">
                    @foreach ($pastScheduledConferences as $scheduledConference)
                        <div class="scheduled-conference sm:flex gap-4">
                            @if ($scheduledConference->hasThumbnail())
                                <div class="scheduled-conference-cover max-w-40">
                                    <img src="{{ $scheduledConference->getThumbnailUrl() }}" alt="{{ $scheduledConference->title }}">
                                </div>
                            @endif
                            <div class="information flex-1 space-y-1">
                                <h3 class="">
                                    <a href="{{ $scheduledConference->getHomeUrl() }}"
                                        class="conference-name link link-primary link-hover font-medium">{{ $scheduledConference->title }}</a>
                                </h3>

                                @if ($scheduledConference->getMeta('summary'))
                                    <div class="scheduled-conference-summary user-content">
                                        {!! $scheduledConference->getMeta('summary') !!}
                                    </div>
                                @endif
                                <a href="{{ $scheduledConference->getHomeUrl() }}" class="link link-primary text-sm">View Current Event</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    @endif
</x-website::layouts.main>
