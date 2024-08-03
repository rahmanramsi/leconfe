@use('App\Constants\SubmissionFileCategory')
@use('App\Models\Enums\SubmissionStatus')

@php
    $galleys = $submission->galleys()->with('file.media')->get();
@endphp

<x-website::layouts.main>
    <div id="submission-detail">
        <div class="mb-6">
            <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
        </div>
        @if($this->canPreview())
            <div class="mb-6">
                <x-website::preview-alert />
            </div>
        @endif
        <h1 class="citation_title text-2xl">
            {{ $submission->getMeta('title') }}
        </h1>
        <div class="mb-4 text-sm text-slate-400">
            <span class="flex items-center">
                <x-lineawesome-calendar-check-solid class="w-3 h-3 mr-0.5" />
                {{ __('Date Published') . ': ' . ($submission->published_at && $submission->isPublished() ? $submission->published_at->format(Setting::get('format_date')) : '-')  }}
            </span>
        </div>
        @if($submission->getFirstMediaUrl('article_cover'))
            <div class="mb-4 max-w-[48rem]">
                <img class="w-full" src="{{ $submission->getFirstMediaUrl('article-cover') }}" alt="article-cover">
            </div>
        @endif
        <div class="submission-detail space-y-7">
            <section class="contributors">
                <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                    {{ __('Contributors') }}
                </h2>
                <div
                    class="grid grid-cols-2 gap-4 p-5 mt-3 border rounded-md shadow-sm bg-slate-100 border-slate-200 text-slate-700">
                    @foreach ($submission->authors as $contributor)
                        <div class="col-span-2 sm:col-span-1">
                            <div class="flex items-center">
                                <x-lineawesome-user class="w-5 h-5 mr-1" />
                                {{ $contributor->fullName }}
                            </div>
                            <span class="ml-[25px] text-sm text-slate-500">{{ $contributor->role->name }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
            @if($submission->getMeta('keywords'))
                <section class="keywords">
                    <div class="mt-4 text-slate-800">
                        <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                            {{ __('Keywords') }}
                        </h2>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($submission->getMeta('keywords') as $keyword)
                                <span 
                                    class="flex items-center px-2 py-1 transition duration-200 ease-in-out border rounded-md shadow-sm bg-slate-100 border-slate-200 link-primary hover:bg-slate-200 hover:border-slate-300">
                                    {{ $keyword }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            <section clas="abstract">
                <div class="mt-4 text-slate-800">
                    <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                        {{ __('Abstract') }}
                    </h2>
                    {!! $submission->getMeta('abstract') !!}
                </div>
            </section>
            <section class="references">
                <div class="mt-4 text-slate-800" id="references">
                    <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                        {{ __('References') }}
                    </h2>
                    @if ($references = $submission->getMeta('references'))
                        {!! $references !!}
                    @else
                        <span class=" text-slate-400">
                            {{ __('No References') }}
                        </span>
                    @endif
                </div>
            </section>
            @if($galleys->isNotEmpty())
                <section class="downloads">
                    <div class="mt-4 text-slate-800">
                        <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                            {{ __('Downloads') }}
                        </h2>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach ($galleys as $galley)
                                <x-scheduledConference::galley-link :galley="$galley"/>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

        </div>
    </div>
</x-website::layouts.main>
