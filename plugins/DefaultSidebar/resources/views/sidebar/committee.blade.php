@if ($committees->isNotEmpty())
    <x-website::sidebar class="sidebar-committees space-y-1" :id="$id">
        <h2 class="text-heading">Committees</h2>
        @foreach ($committees as $committee)
            <div class="bg-white border card card-compact">
                <div class="gap-2 card-body">
                    <div class="flex flex-col gap-4">
                        <div class="flex gap-x-2">
                            @if ($committee->hasMedia('profile'))
                                <div class="profile-image avatar">
                                    <div class="w-12 h-12 rounded-full">
                                        <img src="{{ $committee->getFirstMedia('profile')->getAvailableUrl(['avatar', 'thumb', 'thumb-xl']) }}"
                                            alt="{{ $committee->fullName }}" />
                                    </div>
                                </div>
                            @endif
                            <div class="profile-description space-y-1">
                                <p class="text-content">{{ $committee->fullName }}</p>
                                @if ($committee->getMeta('affiliation'))
                                    <span class="text-xs">{{ $committee->getMeta('affiliation') }}</span>
                                @endif
                                @if($committee->getMeta('scopus_url') || $committee->getMeta('google_scholar_url') || $committee->getMeta('orcid_url'))
                                    <div class="flex items-center gap-1">
                                        @if($committee->getMeta('orcid_url'))
                                        <a href="{{ $committee->getMeta('orcid_url') }}" target="_blank">
                                            <x-academicon-orcid class="w-5 h-5 text-[#A1C837]" />
                                        </a>
                                        @endif
                                        @if($committee->getMeta('google_scholar_url'))
                                        <a href="{{ $committee->getMeta('google_scholar_url') }}" target="_blank">
                                            <x-academicon-google-scholar class="w-5 h-5 text-[#4185F4]" />
                                        </a>
                                        @endif
                                        @if($committee->getMeta('scopus_url'))
                                        <a href="{{ $committee->getMeta('scopus_url') }}" target="_blank">
                                            <x-academicon-scopus class="w-5 h-5 text-[#e9711c]" />
                                        </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="flex justify-end w-full pt-1">
            <a href="{{ route('livewirePageGroup.scheduledConference.pages.committees') }}" class="link link-primary text-sm">
                More
            </a>
        </div>
    </x-website::sidebar>
@endif
