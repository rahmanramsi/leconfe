@use('App\Models\Enums\RegistrationPaymentState')
@use('Illuminate\Support\Str')
@use('Carbon\Carbon')
@use('App\Facades\Setting')
<x-website::layouts.main>
    <div class="space-y-6">
        <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
    </div>

    <div class="mt-6">
        <div class="flex mb-5 space-x-4">
            <h1 class="text-xl font-semibold min-w-fit">Participant Attendance</h1>
            <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
        </div>
        <div class="mt-5 px-6 py-4 margin-2 border text-center">
            <h1 class="text-base text-gray-800 font-semibold">
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
                        <tr class="bg-gray-50 border-b dark:bg-gray-800">
                            <td class="px-6 py-4" colspan="2">
                                <strong class="block font-medium text-gray-900 dark:text-white">
                                    {{ $timeline->name }}
                                    @if ($timeline->isOngoing())
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold mx-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                            Ongoing
                                        </span>
                                    @elseif ($timeline->getEarliestTime()->isFuture())
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold mx-2 px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">
                                            Not started
                                        </span>
                                    @elseif ($timeline->getLatestTime()->isPast())
                                        <span class="bg-gray-200 text-gray-800 text-xs font-semibold mx-2 px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300">
                                            Over
                                        </span>
                                    @endif
                                </strong>
                                <p class="text-gray-500">
                                    {{ Carbon::parse($timeline->date)->format(Setting::get('format_date')) }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($timeline->canAttend() && $isParticipant)
                                    @if ($timeline->isUserAttended(auth()->user()))
                                        <button class="py-1 px-4 text-xs font-medium text-green-700 focus:outline-none bg-white rounded-full border border-gray-200 focus:z-10 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 cursor-default">
                                            Attendance Confirmed
                                        </button>
                                    @else
                                        <button class="py-1 px-4 text-xs font-medium text-blue-700 focus:outline-none bg-white rounded-full border border-gray-200 hover:bg-gray-100 hover:text-blue-600 focus:z-10 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700" 
                                        wire:click="attend({{ $timeline->id }}, '{{ static::ATTEND_TYPE_TIMELINE }}')">
                                            Confirm Attendance
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @php
                            $agendas = $timeline
                                ->agendas()
                                ->orderBy('time_start', 'ASC')
                                ->orderBy('time_end', 'ASC')
                                ->get();
                        @endphp
                        @foreach ($agendas as $agenda)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 pl-8 py-4 text-left w-fit text-nowrap">
                                    {{ $agenda->time_span }}
                                    @if ($agenda->isOngoing())
                                        <span class="bg-green-100 text-green-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                            Ongoing
                                        </span>
                                    @elseif ($agenda->isFuture())
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">
                                            Not started
                                        </span>
                                    @elseif ($agenda->isPast())
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300">
                                            Over
                                        </span>
                                    @endif
                                </td>
                                <th scope="row" class="px-6 py-4 whitespace-nowrap text-left text-wrap" 
                                x-data="{ open: false }">
                                    <strong class="font-medium text-gray-900 dark:text-white">
                                        {{ $agenda->name }}
                                    </strong>

                                    <p class="font-normal text-xs text-gray-500">
                                        {{ new Illuminate\Support\HtmlString($agenda->public_details) }}
                                    </p>

                                    @if (!empty($agenda->details) && $isParticipant)
                                        <div class="flex w-full mt-2 px-4 py-2 rounded hover:!bg-yellow-100 cursor-pointer" style="background-color: #fff8e8;" @click="open = !open">
                                            <x-filament::icon
                                                icon="heroicon-m-lock-open"
                                                class="h-4 w-4 text-gray-500 dark:text-gray-400 "
                                            />
                                                
                                            <span class="flex-1 mx-2 w-auto">
                                                Participant Information
                                            </span>

                                            <div class="flex-1 text-right">
                                                <x-filament::icon
                                                    icon="heroicon-m-chevron-down"
                                                    class="h-4 w-4 text-gray-500 dark:text-gray-400 float-end"
                                                    x-show="!open" 
                                                />
                                                <x-filament::icon
                                                    icon="heroicon-m-chevron-up"
                                                    class="h-4 w-4 text-gray-500 dark:text-gray-400 float-end"
                                                    x-show="open" 
                                                />
                                            </div>
                                        </div>

                                        <div 
                                            class="w-full p-4 rounded cursor-pointer border-t" 
                                            style="background-color: #fff8e8;" 
                                            x-show="open" 
                                            @click.away="open = false"
                                        >
                                            {{ new Illuminate\Support\HtmlString($agenda->details) }}
                                        </div>
                                    @endif
                                </th>
                                <td class="px-4 py-4 text-right align-middle">
                                    @if ($agenda->canAttend() && !$timeline->canAttend() && $isParticipant)
                                        @if ($agenda->isUserAttended(auth()->user()))
                                            <button class="py-1 px-4 text-xs font-medium text-green-700 focus:outline-none bg-white rounded-full border border-gray-200 focus:z-10 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 cursor-default">
                                                Attendance Confirmed
                                            </button>
                                        @else
                                            <button class="py-1 px-4 text-xs font-medium text-blue-700 focus:outline-none bg-white rounded-full border border-gray-200 hover:bg-gray-100 hover:text-blue-600 focus:z-10 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700" 
                                            wire:click="attend({{ $agenda->id }}, '{{ static::ATTEND_TYPE_AGENDA }}')">
                                                Confirm Attendance
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- modal --}}
    @if ($timelineData || $agendaData)
        <div x-data="{ open: @entangle('isOpen') }" x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <div wire:click="cancel" class="fixed inset-0 bg-gray-800 opacity-75"></div>
            <div x-show="open" class="bg-white rounded shadow-lg p-6 w-1/3 mx-auto z-50 transform transition-all duration-300 ease-in-out">
                <div class="flex justify-between items-center border-b pb-3">
                    <h2 class="text-lg font-semibold">
                        Attendance Confirmation
                    </h2>
                </div>
                <div class="mt-4">
                    @if ($typeData === self::ATTEND_TYPE_TIMELINE)
                        <p class="text-gray-600">
                            Are you sure you want attend on <strong>{{ $timelineData->name }}</strong> in <strong>{{ $currentScheduledConference->title }}</strong>?
                        </p>
                    @elseif($typeData === self::ATTEND_TYPE_AGENDA)
                        <p class="text-gray-600">
                            Are you sure you want attend on <strong>{{ $agendaData->name }}</strong> from <strong>{{ $agendaData->timeline->name }}</strong> in <strong>{{ $currentScheduledConference->title }}</strong>?
                        </p>
                    @else
                        INVALID!
                    @endif

                    @if (!empty($errorMessage))
                        <small class="block text-red-500">
                            *{{ $errorMessage }}
                        </small>
                    @endif
                </div>
                <div class="mt-6 flex justify-end space-x-2 text-sm">
                    <button wire:click="cancel" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300" wire:loading.attr="disabled">
                        Cancel
                    </button>
                    <button wire:click="confirm" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" wire:loading.attr="disabled">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-website::layouts.main>
                        