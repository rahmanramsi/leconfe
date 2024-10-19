<div class="w-full">
    <div x-calendar="{{ json_encode($timelines) }}" class="mx-auto"></div>
    <div class="flex justify-end w-full pt-1">
        <a href="{{ route('livewirePageGroup.scheduledConference.pages.timelines') }}" class="link link-primary text-sm">
            Details
        </a>
    </div>
</div>