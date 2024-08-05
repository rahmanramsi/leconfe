@use('Illuminate\Support\Str')
@use('Carbon\Carbon')
@use('App\Facades\Setting')
<x-website::layouts.main>
    <div class="space-y-6 text-center">
        <h1 class="text-2xl font-bold">
            Participant Attendance
        </h1>
    </div>
    <div class="mt-5 px-6 py-4 margin-2 border text-center">
        <h1 class="text-base text-black font-semibold">
            {{ $currentScheduledConference->title }}
        </h1>
        @if (!empty($currentScheduledConference->getMeta('description')))
            <p class="mt-2 text-sm">
                {{ $currentScheduledConference->getMeta('description') }}
            </p>
        @endif
    </div>
    <p class="mt-4 text-sm">
        Please select the event below to confirm your attendance.
    </p>
    <div class="mt-4 relative overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border" lazy>
            <tbody>
                @foreach ($timelines as $timeline)
                    @if ($timeline->hide)
                        @continue
                    @endif
                    <tr class="cursor-pointer bg-white border-b dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600" wire:click="openModal">
                        <td class="px-6 py-4" colspan="3">
                            <strong class="block font-medium text-gray-900 dark:text-white">
                                {{ $timeline->name }}
                            </strong>
                            <p class="text-gray-500">
                                {{ Carbon::parse($timeline->date)->format(Setting::get('format_date')) }}
                            </p>
                        </td>
                    </tr>
                    {{-- 
                        TODO: Optimize this 
                    --}}
                    @php
                        $agendas = $timeline
                            ->agendas()
                            ->orderBy('time_start', 'ASC')
                            ->get();
                    @endphp
                    @foreach ($agendas as $agenda)
                        @if ($agenda->hide)
                            @continue
                        @endif
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">
                                {{ $agenda->time_span }}
                            </td>
                            <th scope="row" class="px-6 py-4 whitespace-nowrap">
                                <strong class="font-medium text-gray-900 dark:text-white">
                                    {{ $agenda->name }}
                                </strong>
                                <p class="font-normal text-gray-500">
                                    {{ new Illuminate\Support\HtmlString($agenda->details) }}
                                </p>
                            </th>
                            <td class="px-6 py-4">
                                {{-- {{ $agenda->isFuture() ? 'future' : 'not future' }}
                                <br>
                                {{ $agenda->isPast() ? 'past' : 'not past' }} --}}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- modal --}}
    <div x-data="{ open: @entangle('isOpen') }" @keydown.escape.window="open = false" x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="fixed inset-0 bg-gray-800 opacity-75"></div>
        <div x-show="open" class="bg-white rounded shadow-lg p-6 w-1/3 mx-auto z-50 transform transition-all duration-300 ease-in-out" @click.away="open = false">
            <div class="flex justify-between items-center border-b pb-3">
                <h2 class="text-lg font-semibold">
                    Attendance Confirmation
                </h2>
            </div>
            <div class="mt-4">
                <p class="text-gray-600">Modal content goes here...</p>
            </div>
            <div class="mt-6 flex justify-end space-x-2 text-sm">
                <button @click="open = false" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Confirm</button>
            </div>
        </div>
    </div>
</x-website::layouts.main>
                        