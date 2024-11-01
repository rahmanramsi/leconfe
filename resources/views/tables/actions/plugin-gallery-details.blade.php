@php($latestCompatibleRelease = $record->getLatestCompatibleRelease())

<div class="grid sm:grid-cols-12 gap-y-4">
    <div class="plugin-information sm:col-span-7 sm:pe-3">
        @if ($record->cover)
            <img src="{{ $record->cover }}" alt="" class="w-auto">
        @endif
        @if($record->description)
            <div class="prose-sm max-w-none ">
                {{ new Illuminate\Support\HtmlString($record->description) }}
            </div>
        @else 
            <p class="text-gray-600">No description available</p>
        @endif
    </div>
    <div class="sm:col-span-5 text-sm sm:ps-3 relative overflow-x-auto">
        @if ($latestCompatibleRelease)
            <table class="w-full border">
                <tbody>
                    @if ($record->isUpgradable() && auth()->user()->can('install', $record))
                        <tr class="border-b">
                            <td class="p-3">{{ $action->getModalAction('upgrade') }}</td>
                        </tr>
                    @endif
                    @if (!$record->isInstalled() && auth()->user()->can('install', $record))
                        <tr class="border-b">
                            <td class="p-3">{{ $action->getModalAction('install') }}</td>
                        </tr>
                    @endif

                    @if ($record->author)
                        <tr class="border-b">
                            <td class="p-3">Author: {{ $record->author }}</td>
                        </tr>
                    @endif
                    @if ($record->homepage)
                        <tr class="border-b">
                            <td class="p-3">Home: <a href="{{ $record->homepage }}" class="text-primary-600 break-all">{{ $record->homepage }}</a></td>
                        </tr>
                    @endif
                    @if ($latestCompatibleRelease->get('version'))
                        <tr class="border-b">
                            <td class="p-3">Version: {{ $latestCompatibleRelease->get('version') }}</td>
                        </tr>
                    @endif

                    @if ($latestCompatibleRelease->get('released_at'))
                        <tr class="border-b">
                            <td class="p-3">Release date:
                                {{ Carbon\Carbon::parse($latestCompatibleRelease->get('released_at'))->format(Setting::get('format_date')) }}
                            </td>
                        </tr>
                    @endif

                    @if ($latestCompatibleRelease->get('note'))
                        <tr class="border-b">
                            <td class="p-3">
                                <p class='font-medium'>Note : </p>
                                <div class="prose prose-sm">
                                    {!! $latestCompatibleRelease->get('note') !!}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endif
    </div>
</div>
