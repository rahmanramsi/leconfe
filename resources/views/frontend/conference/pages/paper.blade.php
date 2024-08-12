@use('App\Constants\SubmissionFileCategory')
@use('App\Models\Enums\SubmissionStatus')

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
        <h1 class="text-2xl citation_title">
            {{ $paper->getMeta('title') }}
        </h1>
        <div class="mb-4 text-sm text-slate-400">
            <span class="flex items-center ">
                <x-lineawesome-calendar-check-solid class="w-3 h-3 mr-0.5" />
                <span class="citation_publication_date">{{ __('Date Published') . ': ' . ($paper->published_at && $paper->isPublished() ? $paper->published_at->format(Setting::get('format_date')) : '-')  }}</span>
            </span>
        </div>
        @if($paper->getFirstMedia('cover'))
            <div class="mb-4 max-w-[48rem]">
                <img class="w-auto" src="{{ $paper->getFirstMedia('cover')->getAvailableUrl(['thumb']) }}" alt="paper-cover">
            </div>
        @endif
        <div class="submission-detail space-y-7">
            <section class="contributors">
                <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                    {{ __('Contributors') }}
                </h2>
                <div
                    class="grid grid-cols-2 gap-4 p-4 mt-3 border rounded-md shadow-sm content bg-slate-100 border-slate-200 text-slate-700">
                    @foreach ($paper->authors as $contributor)
                        <div class="col-span-2 sm:col-span-1">
                            <div class="flex items-center">
                                <x-lineawesome-user class="w-5 h-5 mr-1" />
                                <h3 class="citation_author">{{ $contributor->fullName }}</h3>
                            </div>
                            <div class="ml-[25px] text-sm text-slate-500">{{ $contributor->role->name }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
            @if($paper->getMeta('keywords'))
                <section class="mt-4 keywords">
                    <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                        {{ __('Keywords') }}
                    </h2>
                    <div class="content text-slate-800">
                        <div class="flex flex-wrap gap-3">
                            @foreach ($paper->getMeta('keywords') as $keyword)
                                <span
                                    class="flex items-center px-2 py-1 text-xs border rounded-md shadow-sm bg-slate-100 border-slate-200 link-primary">
                                    {{ $keyword }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            <section clas="abstract mt-4">
                <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                    {{ __('Abstract') }}
                </h2>
                <div class="citation_abstract content text-slate-800">
                    {!! $paper->getMeta('abstract') !!}
                </div>
            </section>
            <section class="mt-4 references">
                <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                    {{ __('References') }}
                </h2>
                <ol class="content text-slate-800">
                    @if ($paper->getMeta('references'))
                        @foreach(collect(explode(PHP_EOL, $this->paper->getMeta('references')))->filter()->values() as $reference)
                            <li class="reference">{{ $reference }}</li>
                        @endforeach
                    @else
                        <span class=" text-slate-400">
                            {{ __('No References') }}
                        </span>
                    @endif
                </ol>
            </section>
            @if($paper->galleys->isNotEmpty())
                <section class="mt-4 downloads">
                    <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
                        {{ __('Downloads') }}
                    </h2>
                    <div class="mt-4 content text-slate-800">
                        <div class="download flex flex-wrap gap-1.5 mt-2">
                            @foreach ($paper->galleys as $galley)
                                <x-scheduledConference::galley-link :galley="$galley"/>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

        </div>
    </div>
</x-website::layouts.main>
