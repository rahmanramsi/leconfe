<a href="{{ $scheduledConference->getHomeUrl() }}" class="relative flex cursor-pointer select-none items-center rounded px-1 py-1.5 text-sm outline-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 hover:bg-primary hover:text-white gap-x-2 focus:bg-primary focus:text-white">
	<div class="cover w-6 max-w-6 h-auto grow">
		@if($scheduledConference->hasThumbnail())
			<img src="{{ $scheduledConference->getThumbnailUrl() }}" alt="{{ $scheduledConference->title }}">
		@endif
	</div>
	<span>{{ $scheduledConference->title }}</span>
</a>