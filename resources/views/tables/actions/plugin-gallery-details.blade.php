@php($latestCompatibleRelease = $record->getLatestCompatibleRelease())

<div class="grid sm:grid-cols-12 gap-y-4">
	<div class="sm:col-span-7 prose-sm max-w-none sm:pe-3">
		{{ new Illuminate\Support\HtmlString($record->description) }}
	</div>
	<div class="sm:col-span-5 text-sm sm:ps-3 relative overflow-x-auto">
		@if($latestCompatibleRelease)
		<table class="w-full border">
			<tbody>
				@if($record->isUpgradable())
					<tr class="border-b">
						<td class="p-3">{{ $action->getModalAction('upgrade') }}</td>
					</tr>
				@endif
				@if(!$record->isInstalled())
					<tr class="border-b">
						<td class="p-3">{{ $action->getModalAction('install') }}</td>
					</tr>
				@endif
				<tr class="border-b">
					<td class="p-3">Version: {{ $latestCompatibleRelease['version'] }}</td>
				</tr>
				<tr class="border-b">
					<td class="p-3">Release date: {{ Carbon\Carbon::parse($latestCompatibleRelease['released_at'])->format(Setting::get('format_date')) }}</td>
				</tr>
			</tbody>
		</table>
		@endif
	</div>
</div>