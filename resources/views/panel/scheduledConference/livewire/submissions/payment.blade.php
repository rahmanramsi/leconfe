<div class="space-y-6">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-8 space-y-4">
            @if ($isRegistrationOpen)
                @if ($authorRegistration)
                    @if ($authorRegistration->registrationPayment->level === App\Models\RegistrationType::LEVEL_AUTHOR)
                        @if ($authorRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value)
                            <x-filament::section>
                                <div class="grid w-full grid-cols-11 px-6 py-3 rounded-md shadow-inner">
                                    <div class="col-span-1 avatar">
                                        <img src="{{ $author->getFilamentAvatarUrl() }}" alt="Profile Picture" class="rounded-full h-min">
                                    </div>
                                    <div class="inline-block col-span-10 my-auto text-sm">
                                        <strong class="font-semibold">
                                            {{ $author->full_name }}
                                        </strong>
                                        <p>
                                            {{ __('general.verified_author') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-5">
                                    <p>
                                        {{ __('general.you_registered_as_participant_author') }}
                                    </p>
                                    <p class="mt-3">
                                        {{ __('general.please_wait_for_editor_review_your_submission') }}
                                    </p>
                                </div>
                            </x-filament::section>
                        @else
                            <x-filament::section>
                                <p>
                                    {{ __('general.you_not_finish_payment_process') }}
                                </p>
                                <p class="mt-3">
                                    {!! __('general.see_your_registration_status', ['route' => route('livewirePageGroup.scheduledConference.pages.participant-registration')]) !!}
                                </p>
                            </x-filament::section>
                        @endif
                    @else
                        <x-filament::section>
                            <p>
                                {{ __('general.you_did_not_register_as_an_author') }}
                            </p>
                        </x-filament::section>
                    @endif

                    <x-filament::section>
                        <x-slot name="heading">
                            {{ __('general.author_registration_details') }}
                        </x-slot>

                        <x-slot name="headerEnd">
                            <a href="{{ route('livewirePageGroup.scheduledConference.pages.participant-registration') }}" class="text-sm text-blue-500 hover:text-blue-700 hover:underline">{{ __('general.registration_page') }} &rsaquo;</a>
                        </x-slot>

                        <table class="w-full text-sm">
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.status') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-2 text-left">
                                    {{ $author->full_name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.status') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-2 text-left">
                                    @if ($authorRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value && !$authorRegistration->trashed())
                                        <x-filament::badge color="success" class="!w-fit">
                                            {{ __('general.paid') }}
                                        </x-filament::badge>
                                    @elseif ($authorRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Unpaid->value && !$authorRegistration->trashed())
                                        <x-filament::badge color="warning" class="!w-fit">
                                            {{ __('general.unpaid') }}
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="error" class="!w-fit">
                                            {{ __('general.fail') }}
                                        </x-filament::badge>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.type') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-2 text-left">
                                    {{ $authorRegistration->registrationPayment->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.description') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-2 text-left">
                                    {{ $authorRegistration->registrationPayment->description }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.cost') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-2 text-left">
                                    {{ fixedMoney($authorRegistration->registrationPayment->cost, $authorRegistration->registrationPayment->currency, true) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.registration_date') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-2 text-left">
                                    {{ $authorRegistration->registrationPayment->created_at->format(App\Facades\Setting::get('format_date')) }}
                                </td>
                            </tr>
                            @if ($authorRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value && $authorRegistration->registrationType->currency !== 'free')
                                <tr>
                                    <td class="font-semibold w-fit">{{ __('general.payment_date') }}</td>
                                    <td class="pl-3">:</td>
                                    <td class="py-2 text-left">
                                        {{ $authorRegistration->registrationPayment->paid_at->format(App\Facades\Setting::get('format_date')) }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </x-filament::section>
                @else
                    <x-filament::section>
                        <p>
                            {!! __('general.you_are_not_participant_of_this_conference', ['route' => route('livewirePageGroup.scheduledConference.pages.participant-registration')]) !!}
                        </p>
                        <p class="mt-3">
                            {{ __('general.make_sure_to_register_as_an_author_not_participant') }}
                        </p>
                    </x-filament::section>
                @endif
            @else
                <x-filament::section>
                    <p>
                        {{ __('general.registration_are_closed') }}
                    </p>
                </x-filament::section>
            @endif
        </div>
        <div class="col-span-4 gap-2 space-y-4">

            {{-- Participants --}}
            @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\ParticipantList::class, ['submission' => $submission])

            @if ($isRegistrationOpen)

                {{-- Payment Manual List --}}
                @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\PaymentManualList::class, ['submission' => $submission])

            @endif

        </div>
    </div>
</div>
