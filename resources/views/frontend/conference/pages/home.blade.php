<x-website::layouts.main>
    <div class="space-y-2">
        <section id="highlight-conference" class="p-5 space-y-4">
            <div class="flex flex-col sm:flex-row flex-wrap space-y-4 sm:space-y-0 gap-4">
                <div class="flex flex-col gap-4 flex-1">
                    <div>
                        <img src="https://placehold.co/960x200" class="w-full" alt="">
                    </div>
                    <div class="inline-flex items-center space-x-2">
                        <h1 class="cf-name text-2xl">International Conference of MATHEMATICS AND ITS Application
                            Artificial Intelligent on ChatGPT and the Effect</h1>
                        <div class="badge bg-purple-300 rounded-full px-3 text-xs flex items-center justify-center"
                            style="height: 2rem;">{{ $currentConference->type }}</div>
                    </div>
                    @if ($currentConference->getMeta('description'))
                        <div class="user-content">
                            {{ $currentConference->getMeta('description') }}
                        </div>
                    @endif
                    @if ($currentConference->series->where('active', true)->first()->description)
                        <div>
                            <h2 class="text-base font-medium">Series Description :</h2>
                            <div class="user-content">
                                {{ $currentConference->series->where('active', true)->first()->description }}
                            </div>
                        </div>
                    @endif
                    @if ($topics->isNotEmpty())
                        <div>
                            <h2 class="text-base font-medium mb-1">Topics :</h2>
                            <div class="flex flex-wrap w-full gap-2">
                                @foreach ($topics as $topic)
                                    <span
                                        class="badge badge-outline text-xs border border-gray-300 h-6 text-secondary">{{ $topic->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div>
                        <a href="#"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 w-fit">
                            <x-heroicon-o-document-arrow-up class="h-5 w-5 mr-2" />
                            Submit Now
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="conference-partner" class="p-5 space-y-4">
            @if ($currentConference->sponsors)
                <div class="sponsors space-y-4" x-data="carousel">
                    <h2 class="text-xl text-center">Conference Partner</h2>
                    <div class="sponsors-carousel flex items-center w-full gap-4" x-bind="carousel">
                        <button x-on:click="toLeft"
                            class="hidden bg-gray-400 hover:bg-gray-500 h-10 w-10 rounded-full md:flex items-center justify-center">
                            <x-heroicon-m-chevron-left class="h-6 w-fit text-white" />
                        </button>
                        <ul x-ref="slider"
                            class="flex-1 flex w-full snap-x snap-mandatory overflow-x-scroll gap-3 py-4">
                            @foreach ($currentConference->sponsors as $sponsor)
                                <li @class([
                                    'flex shrink-0 snap-start flex-col items-center justify-center',
                                    'ml-auto' => $loop->first,
                                    'mr-auto' => $loop->last,
                                ])>
                                    <img class="max-h-24 w-fit"
                                        src="{{ $sponsor->getFirstMedia('logo')?->getAvailableUrl(['thumb']) }}"
                                        alt="{{ $sponsor->name }}">
                                </li>
                            @endforeach
                        </ul>
                        <button x-on:click="toRight"
                            class="hidden bg-gray-400 hover:bg-gray-500 h-10 w-10 rounded-full md:flex items-center justify-center">
                            <x-heroicon-m-chevron-right class="h-6 w-fit text-white" />
                        </button>
                    </div>
                </div>
            @endif
        </section>
        <section id="conference-detail-tab" class="p-5 space-y-4">
            <div x-data="{ activeTab: 'information' }" class=" bg-white">
                <div class="border border-t-0 border-x-0 border-gray-300">
                    <button @click="activeTab = 'information'"
                        :class="{ 'text-blue-400 ': activeTab === 'information' }"
                        class="px-4 py-2 text-sm hover:text-blue-400 border border-b-white  border-gray-300" style="margin-bottom: -1px">Information</button>
                    <button @click="activeTab = 'participant-info'"
                        :class="{ 'text-blue-400 ': activeTab === 'participant-info' }"
                        class="px-4 py-2 text-sm hover:text-blue-400 border border-b-white border-gray-300" style="margin-bottom: -1px">Participant Info</button>
                    <button @click="activeTab = 'registration-info'"
                        :class="{ 'text-blue-400 ': activeTab === 'registration-info' }"
                        class="px-4 py-2 text-sm hover:text-blue-400 border border-b-white border-gray-300" style="margin-bottom: -1px">Registration Info</button>
                </div>
                <div class="">

                    <div x-show="activeTab === 'information'" class="p-4 border border-t-0 border-gray-300 ">
                        <div class="text-content">
                            <table>
                                <tr>
                                    <td>Type</td>
                                    <td class="pl-5 pr-2">:</td>
                                    <td>{{ $currentConference->type }}</td>
                                </tr>
                                <tr>
                                    <td>Place</td>
                                    <td class="pl-5 pr-2">:</td>
                                    <td>{{ $currentConference->type }}</td>
                                </tr>
                                <tr>
                                    <td>Date</td>
                                    <td class="pl-5 pr-2">:</td>
                                    <td>{{ $currentConference->type }}</td>
                                </tr>
                                <tr>
                                    <td>Organized by</td>
                                    <td class="pl-5 pr-2">:</td>
                                    <td>{{ $currentConference->type }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div x-show="activeTab === 'participant-info'" class="p-4 border border-t-0 border-gray-300 ">
                        <p>Content for second tab.</p>
                    </div>
                    <div x-show="activeTab === 'registration-info'" class="p-4 border border-t-0 border-gray-300 ">
                        <p>Content for third tab.</p>
                    </div>
                    <div x-show="activeTab === 'contact-info'" class="p-4 border border-t-0 border-gray-300 ">
                        <p>Content for fifth tab.</p>
                    </div>
                    <div x-show="activeTab === 'editorial-comittee'" class="p-4 border border-t-0 border-gray-300 ">
                        <p>Content for sixth tab.</p>
                    </div>
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
                                {{ date(setting('format.date'), strtotime($currentConference->date_start)) }} -
                                {{ date(setting('format.date'), strtotime($currentConference->date_end)) }}
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
                                <img class="max-w-[100px]"
                                    src="{{ $venue->getFirstMedia('thumbnail')->getAvailableUrl(['thumb', 'thumb-xl']) }}">
                            @endif
                            <div class="space-y-2">
                                <div>
                                    <a
                                        class="group/link relative inline-flex items-center justify-center outline-none gap-1 font-thin">
                                        <span
                                            class="font-semibold group-hover/link:underline group-focus-visible/link:underline text-base">
                                            {{ $venue->name }}
                                        </span>
                                    </a>
                                    <p class="text-gray-500 text-sm flex items-center gap-1"><x-heroicon-m-map-pin
                                            class="size-4" /> {{ $venue->location }}</p>
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
