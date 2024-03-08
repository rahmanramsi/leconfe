<x-block :id="$id" class="flex flex-col space-y-1">
    @if ($archives->isNotEmpty())
        <h2 class="text-heading px-2">Previous Event</h2>
        @foreach ($archives as $archive)
            <div class="card card-compact bg-white w-full p-4 flex-col rounded">
                <div class="w-full flex justify-between text-primary">
                    <a href="{{ route('livewirePageGroup.archive-conference.pages.home', ['conference' => $archive->path]) }}"
                        class="text-sm block hover:text-primary-focus">{{ $archive->name }}</a>
                    <div class="inline-flex gap-x-1 text-sm items-center">
                        @if ($archive->start_date)
                            <time>{{ date('Y', strtotime($archive->start_date)) }}</time>
                        @endif
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</x-block>
