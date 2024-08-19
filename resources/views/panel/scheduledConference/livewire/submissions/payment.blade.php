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
                                    <div class="inline-block col-span-10 pl-5 my-auto text-sm">
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
                                        {{ __('general.thank_you_for_completing_registration_process') }}
                                    </p>
                                </div>
                            </x-filament::section>
                        @else
                            <x-filament::section>
                                <p>
                                    {!! __('general.please_finish_your_payment_registration_process', ['route' => route('livewirePageGroup.scheduledConference.pages.participant-registration')]) !!}
                                </p>
                            </x-filament::section>
                        @endif
                    @else
                        <x-filament::section>
                            <p>
                                {{ __('general.registration_currenty_participant_level') }}
                            </p>
                        </x-filament::section>
                    @endif

                    <x-filament::section>
                        <x-slot name="heading">
                            {{ __('general.author_registration_details') }}
                        </x-slot>

                        <x-slot name="description">
                            {!!  __('general.this_registration_details', ['full_name' => $author->full_name])  !!}
                        </x-slot>

                        <x-slot name="headerEnd">
                            <a href="{{ route('livewirePageGroup.scheduledConference.pages.participant-registration') }}" class="text-sm text-blue-500 hover:text-blue-700 hover:underline">{{ __('general.registration_page') }} &rsaquo;</a>
                        </x-slot>

                        <table class="w-full text-sm">
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
                                <td class="font-semibold w-fit">{{ __('general.level') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-2 text-left">
                                    <x-filament::badge color="info" class="!w-fit">
                                        {{
                                            match ($authorRegistration->registrationPayment->level) {
                                                App\Models\RegistrationType::LEVEL_PARTICIPANT => 'Participant',
                                                App\Models\RegistrationType::LEVEL_AUTHOR => 'Author',
                                                default => 'None',
                                            }
                                        }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.cost') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-2 text-left">
                                    {{ moneyOrFree($authorRegistration->registrationPayment->cost, $authorRegistration->registrationPayment->currency, true) }}
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
                        </table>
                    </x-filament::section>
                @else
                    <x-filament::section>
                        <p>
                            {!! __('general.consider_registration_process', ['route' => route('livewirePageGroup.scheduledConference.pages.participant-registration')]) !!}
                        </p>
                    </x-filament::section>
                @endif
            @else
                <x-filament::section>
                    <p>
                        {{ __('general.we_apologize_registration_currenty_closed') }} Look into <a class="text-blue-500 hover:underline" href="{{ route('livewirePageGroup.scheduledConference.pages.agenda') }}">agenda</a> may help you to know when registration open.
                    </p>
                </x-filament::section>
            @endif

            @if($currentScheduledConference->getMeta('support_contact_name') ||
                $currentScheduledConference->getMeta('support_contact_email') ||
                $currentScheduledConference->getMeta('support_contact_phone'))
                <x-filament::section>
                    <x-slot name="heading">
                        {{ __('general.technical_support_contact') }}
                    </x-slot>

                    <x-slot name="description">
                        {!! __('general.have_questin_contact_our_technical_support') !!}
                    </x-slot>

                    <table class="w-full text-sm">
                        <tr>
                            <td class="font-semibold w-fit">{{ __('general.name') }}</td>
                            <td class="pl-3">:</td>
                            <td class="py-2 text-left">
                                {{ $currentScheduledConference->getMeta('support_contact_name') ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-semibold w-fit">{{ __('general.email') }}</td>
                            <td class="pl-3">:</td>
                            <td class="py-2 text-left">
                                {{ $currentScheduledConference->getMeta('support_contact_email') ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-semibold w-fit">{{ __('general.phone') }}</td>
                            <td class="pl-3">:</td>
                            <td class="py-2 text-left">
                                {{ $currentScheduledConference->getMeta('support_contact_phone') ?? '-' }}
                            </td>
                        </tr>
                    </table>
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
