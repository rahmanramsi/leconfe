<x-website::layouts.main>
    <div class="space-y-5">
        @if ($site->getMeta('about'))
            <div class="description user-content">
                {{ new Illuminate\Support\HtmlString($site->getMeta('about')) }}
            </div>
        @endif

        <div class="conferences space-y-4" x-data="{ tab: 'current' }" x-cloak>
            <div class="conference-current space-y-4" x-show="tab === 'current'">
                @if ($conferences->isNotEmpty())
                    <div class="grid xl:grid-cols-2 gap-6">
                        @foreach ($conferences as $conference)
                            <div class="conference sm:flex gap-4">
                                @if ($conference->hasThumbnail())
                                    <div class="cover max-w-40">
                                        <img src="{{ $conference->getThumbnailUrl() }}" alt="{{ $conference->name }}">
                                    </div>
                                @endif
                                <div class="information flex-1 space-y-2">
                                    <h2 class="">
                                        <a href="{{ $conference->getHomeUrl() }}"
                                            class="conference-name link link-primary link-hover font-bold">{{ $conference->name }}</a>
                                    </h2>

                                    @if ($conference->getMeta('summary'))
                                        <div class="conference-summary user-content">
                                            {!! $conference->getMeta('summary') !!}
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-2">
                                        <a href="{{ $conference->getHomeUrl() }}" class="link text-sm">View Conference</a>
                                        <a href="{{ $conference->getHomeUrl() }}" class="link text-sm">Current Event</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center my-12">
                        <p class="text-lg font-bold">There are no conferences taking place at this time</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-website::layouts.main>
