<x-website::layouts.main>
    <div class="space-y-2">
        <section id="highlight-conference" class="p-5 space-y-4">
            {{-- <h1 class="cf-name text-lg">{{ $currentConference->name }}</h1> --}}
            <div class="flex flex-col sm:flex-row flex-wrap space-y-4 sm:space-y-0 gap-4">
                <div class="flex flex-col gap-2 flex-1">
                    @if ($currentConference->hasMedia('thumbnail'))
                        <div class="cf-thumbnail mb-5">
                            <img class="w-full rounded "
                                src="{{ $currentConference->getFirstMedia('thumbnail')->getUrl() }}"
                                alt="{{ $currentConference->name }}" />
                        </div>
                    @endif
                    {{-- <h1 class="cf-name text-xl">{{ $currentConference->name }}</h1> --}}
                    <div class="inline-flex items-center space-x-2">
                        <h1 class="cf-name text-2xl">International Conference of MATHEMATICS AND ITS Application Artificial Intelligent on ChatGPT and the Effect</h1>
                        <div class="badge bg-purple-300 rounded-full px-3 text-xs flex items-center justify-center" style="height: 2rem;">{{ $currentConference->type }}</div>
                    </div>


                    {{-- @if ($currentConference->date_start)
                        <div class="inline-flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                            </svg>
                            <time
                                class="text-xs text-secondary">{{ date(setting('format.date'), strtotime($currentConference->date_start)) }}</time>
                        </div>
                    @endif --}}
                    @if ($currentConference->getMeta('description'))
                        <div class="user-content">
                            {{ $currentConference->getMeta('description') }}
                        </div>
                    @endif
                    {{-- SERIES DESCRIPTION --}}
                    @if ($currentConference->series->where('active', true)->first()->description)
                        <h2 class="text-base font-medium">Series Description :</h2>
                        <div class="user-content">
                            {{ $currentConference->series->where('active', true)->first()->description }}
                        </div>
                        @endif
                        {{-- TOPICS --}}
                    @if ($topics->isNotEmpty())
                        <h2 class="text-base font-medium">Topics :</h2>
                        <div class="flex flex-wrap w-full gap-2">
                            @foreach ($topics as $topic)
                                <span
                                    class="badge badge-outline text-xs border border-gray-300 h-6 text-secondary">{{ $topic->name }}</span>
                            @endforeach
                        </div>
                    @endif
                    {{-- SUBMIT --}}
                </div>
            </div>
        </section>

        @if ($currentConference->date_start || $currentConference->hasMeta('location'))
            <section id="conference-information" class="p-5 flex flex-col gap-2">
                <h2 class="text-heading">Information</h2>
                <table class="w-full text-sm" cellpadding="4">
                    <tr>
                        <td width="80">Type</td>
                        <td width="20">:</td>
                        <td>{{ $currentConference->type }}</td>
                    </tr>
                    @if ($currentConference->hasMeta('location'))
                        <tr>
                            <td>Place</td>
                            <td>:</td>
                            <td>{{ $currentConference->getMeta('location') }}</td>
                        </tr>
                    @endif

                    @if ($currentConference->date_start)
                        <tr>
                            <td>Date</td>
                            <td>:</td>
                            <td>
                                {{ date(setting('format.date'), strtotime($currentConference->date_start)) }} - {{ date(setting('format.date'), strtotime($currentConference->date_end)) }}
                            </td>
                        </tr>
                    @endif
                </table>
            </section>
        @endif

        @if ($participantPosition->isNotEmpty())
            <section id="conference-speakers" class="p-5 flex flex-col gap-2">
                <h2 class="text-heading">Speakers</h2>
                <div class="cf-speakers space-y-6">
                    @foreach ($participantPosition as $position)
                        @if ($position->participants->isNotEmpty())
                            <div class="space-y-4">
                                <h3 class="text-base">{{ $position->name }}</h3>
                                <div class="cf-speaker-list grid sm:grid-cols-2 gap-2">
                                    @foreach ($position->participants as $participant)
                                        <div class="cf-speaker h-full flex gap-2">
                                            <img class="w-16 h-16 object-cover aspect-square rounded-full"
                                                src="{{ $participant->getFilamentAvatarUrl() }}"
                                                alt="{{ $participant->fullName }}" />
                                            <div>
                                                <div class="speaker-name text-sm text-gray-900">
                                                    {{ $participant->fullName }}
                                                </div>
                                                <div class="speaker-meta">
                                                    @if ($participant->getMeta('expertise'))
                                                        <div class="speaker-expertise text-2xs text-primary">
                                                            {{ implode(', ', $participant->getMeta('expertise') ?? []) }}
                                                        </div>
                                                    @endif
                                                    @if ($participant->getMeta('affiliation'))
                                                        <div class="speaker-affiliation text-2xs text-secondary">
                                                            {{ $participant->getMeta('affiliation') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif


        @if ($currentConference->getMeta('additional_content'))
            <section class="user-content px-5">
                {!! $currentConference->getMeta('additional_content') !!}
            </section>
        @endif

        @if ($venues->isNotEmpty())
            <section class="venues p-5">
                <h2 class="text-heading">Venues</h2>
                <div class="venue-list space-y-3">
                    @foreach ($venues as $venue)
                    <div class="venue flex gap-3">
                        @if ($venue->hasMedia('thumbnail'))
                            <img class="max-w-[100px]" src="{{ $venue->getFirstMedia('thumbnail')->getAvailableUrl(['thumb', 'thumb-xl']) }}">
                        @endif
                        <div class="space-y-2">
                            <div>
                                <a class="group/link relative inline-flex items-center justify-center outline-none gap-1 font-thin">
                                    <span
                                        class="font-semibold group-hover/link:underline group-focus-visible/link:underline text-base">
                                        {{ $venue->name }}
                                    </span>
                                </a>
                                <p class="text-gray-500 text-sm flex items-center gap-1"><x-heroicon-m-map-pin class="size-4" /> {{ $venue->location }}</p>
                            </div>
                            <p class="text-gray-500 text-xs">{{ $venue->description }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-website::layouts.main>
