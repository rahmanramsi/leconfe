<x-website::layouts.main>
    <div class="space-y-6">
        <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
        @if ($this->canPreview())
            <x-website::preview-alert />
        @endif
       
        <div class="proceeding-toc space-y-6">
            <div class="proceeding-detail">
                <x-website::heading-title tag="h1" :title="$proceeding->seriesTitle()" />
                <div class="flex flex-col sm:flex-row gap-4">
                    @if($proceeding->getFirstMediaUrl('cover'))
                        <div class="cover max-w-56 grow">
                            <img src="{{ $proceeding->getFirstMediaUrl('cover') }}" class="w-full" alt="Proceeding Cover">
                        </div>
                    @endif
                    <div class="flex-1">
                        <div class="space-y-4">
                            <div class="text-sm text-justify">
                                {{ $proceeding->description }}
                            </div>
                            <div class="text-sm">
                                <span class="font-semibold">Published: </span> {{ $proceeding->published_at ? $proceeding->published_at->format(Setting::get('format_date')) : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tracks">
                @foreach ($tracks as $track)    
                    <x-website::heading-title :title="$track->title" class="track-title"/>
                    <div class="space-y-5">
                        @forelse($track->submissions as $paper)
                            <x-conference::paper-summary :paper="$paper"/>  
                        @empty
                            <div class="text-center text-gray-500">
                                No paper found.
                            </div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-website::layouts.main>