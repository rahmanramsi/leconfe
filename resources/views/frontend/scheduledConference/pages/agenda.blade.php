@use('App\Models\Enums\RegistrationPaymentState')
@use('App\Facades\Setting')

<x-website::layouts.main>
    <div class="space-y-6">
        <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
    </div>

    <div class="mt-6">
        <div class="flex mb-5 space-x-4">
            <h1 class="text-xl font-semibold min-w-fit">Agenda</h1>
            <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
        </div>
        @if ($isParticipant)
            <p class="mt-4 text-sm">
                Please select the event below to confirm your attendance.
            </p>
        @endif
        <div class="mt-4 relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border" lazy>
                <thead>
                    <tr class="border-b bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-100">
                        <td class="px-6 pl-8 py-2 text-left">Time</td>
                        <td class="px-6 py-2 text-center">Session Name</td>
                        @if ($isParticipant)
                            <td class="px-6 py-2 text-center">Status</td>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if ($timelines->isEmpty())
                        <tr>
                            <td class="px-6 py-4 text-center" colspan="3">
                                Agenda are empty.
                            </td>
                        </tr>
                    @endif
                    @foreach ($timelines as $timeline)
                        <tr class="bg-gray-50 border-b dark:bg-gray-800">
                            <td class="px-6 py-4" {!! (!$timeline->canShown() || !$isParticipant) ? "colspan='3'" : "colspan='2'" !!}>
                                <strong class="block font-medium text-gray-900 dark:text-white">
                                    {{ $timeline->name }}
                                    @if ($isParticipant)
                                        @if ($timeline->isOngoing())
                                            <span class="badge text-xs badge-success text-white mx-1">
                                                On going
                                            </span>
                                        @elseif ($timeline->getEarliestTime()->isFuture())
                                            <span class="badge text-xs badge-info text-white mx-1">
                                                Not started
                                            </span>
                                        @elseif ($timeline->getLatestTime()->isPast())
                                            <span class="badge text-xs mx-1">
                                                Over
                                            </span>
                                        @endif
                                    @endif
                                </strong>
                                <p class="text-gray-500">
                                    {{ $timeline->date->format(Setting::get('format_date')) }}
                                </p>
                            </td>
                            @if ($timeline->canShown() && $isParticipant)
                                <td class="px-6 py-4 text-right text-xs font-normal w-fit">
                                    @if ($timeline->canAttend())
                                        @if ($userRegistration->isAttended($timeline))
                                            <button class="btn btn-sm btn-disabled no-animation !text-white hover:text-white">
                                                Confirmed
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-info no-animation text-white rounded" 
                                            wire:click="attend({{ $timeline->id }}, '{{ static::ATTEND_TYPE_TIMELINE }}')">
                                                Attend
                                            </button>
                                        @endif
                                    @else
                                        @if ($userRegistration->isAttended($timeline))
                                            <button class="btn btn-sm btn-disabled no-animation !text-white hover:text-white">
                                                Confirmed
                                            </button>
                                        @else
                                            @if ($timeline->getEarliestTime()->isFuture())
                                                <button class="btn btn-sm btn-disabled no-animation !text-white hover:text-white">
                                                    Incoming
                                                </button>
                                            @elseif ($timeline->getEarliestTime()->isPast())
                                                <button class="btn btn-sm btn-disabled no-animation !text-white hover:text-white">
                                                    Expired
                                                </button>
                                            @endif
                                        @endif
                                    @endif
                                </td>
                            @endif
                        </tr>
                        @php
                            $sessions = $timeline->sessions;
                        @endphp
                        @foreach ($sessions as $session)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 pl-8 py-4 text-left w-fit text-nowrap">
                                    {{ $session->time_span }}
                                    @if ($isParticipant)
                                        @if ($session->isOngoing())
                                            <span class="badge text-xs badge-success  text-white mx-1">
                                                On going
                                            </span>
                                        @elseif ($session->isFuture())
                                            <span class="badge text-xs badge-info text-white mx-1">
                                                Not Started
                                            </span>
                                        @elseif ($session->isPast())
                                            <span class="badge text-xs mx-1">
                                                Over
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td scope="row" class="px-6 py-4 whitespace-nowrap text-left text-wrap w-full"
                                x-data="{ open: false }">
                                    <strong class="font-medium text-gray-900 dark:text-white">
                                        {{ $session->name }}
                                    </strong>

                                    <p class="font-normal text-xs text-gray-500">
                                        {{ new Illuminate\Support\HtmlString($session->public_details) }}
                                    </p>

                                    @if (!empty($session->details) && $isParticipant)
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
                                            {{ new Illuminate\Support\HtmlString($session->details) }}
                                        </div>
                                    @endif
                                </td>
                                @if ($session->require_attendance && !$timeline->isRequireAttendance() && $isParticipant)
                                    <td class="px-4 py-4 text-right align-middle w-fit">
                                        @if ($session->isOngoing())
                                            @if ($userRegistration->isAttended($session))
                                                <button class="btn btn-xs btn-disabled no-animation !text-white hover:text-white">
                                                    Confirmed
                                                </button>
                                            @else
                                                <button class="btn btn-xs btn-info no-animation text-white hover:text-white " 
                                                wire:click="attend({{ $session->id }}, '{{ static::ATTEND_TYPE_SESSION }}')">
                                                    Attend
                                                </button>
                                            @endif
                                        @else
                                            @if ($userRegistration->isAttended($session))
                                                <button class="btn btn-xs btn-disabled no-animation !text-white hover:text-white">
                                                    Confirmed
                                                </button>
                                            @else
                                                @if ($session->isFuture())
                                                    <button class="btn btn-xs btn-disabled no-animation !text-white hover:text-white">
                                                        Incoming
                                                    </button>
                                                @elseif ($session->isPast())
                                                    <button class="btn btn-xs btn-disabled no-animation !text-white hover:text-white">
                                                        Expired
                                                    </button>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- modal --}}
    @if ($timelineData || $sessionData)
        <div x-data="{ open: @entangle('isOpen') }" x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <div wire:click="cancel" class="fixed inset-0 bg-gray-800 opacity-75"></div>

            <div x-show="open" x-transition class="bg-white rounded shadow-lg p-6 w-1/3 mx-auto z-50 transform transition-all duration-300 ease-in-out">
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
                    @elseif($typeData === self::ATTEND_TYPE_SESSION)
                        <p class="text-gray-600">
                            Are you sure you want attend on <strong>{{ $sessionData->name }}</strong> from <strong>{{ $sessionData->timeline->name }}</strong> in <strong>{{ $currentScheduledConference->title }}</strong>?
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
                        