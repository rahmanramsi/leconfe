@use('App\Panel\ScheduledConference\Livewire\Submissions\Components')
@use('App\Models\Enums\SubmissionStage')
@use('App\Models\Enums\SubmissionStatus')

@php
    $user = auth()->user();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-8 space-y-4">
            @if ($isRegistrationOpen)
                @if ($submissionRegistration)
                    @if ($submissionRegistration->registrationPayment->level === App\Models\RegistrationType::LEVEL_AUTHOR)
                        @if ($submissionRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value)
                            @if($isSubmissionAuthor)
                                <x-filament::section>
                                    <p>{{ __('general.thank_you_for_completing_registration_process') }}</p>
                                </x-filament::section>
                            @else
                                <x-filament::section>
                                    <p>This submission has completed the registration process and finished the payment.</p>
                                </x-filament::section>
                            @endif
                        @else
                            @if($isSubmissionAuthor)
                                <x-filament::section>
                                    <p>Please finish the payment to complete registration and continue your submission process.</p>
                                </x-filament::section>
                            @else
                                <x-filament::section>
                                    <p>This submission has not completed its registration payment yet.</p>
                                </x-filament::section>
                            @endif
                        @endif
                    @endif

                    <x-filament::section>
                        <x-slot name="heading">
                            Submission Registration Details
                        </x-slot>

                        <x-slot name="description">
                            These are the registration details of this submission.
                        </x-slot>

                        <x-slot name="headerEnd">
                            
                        </x-slot>

                        <table class="w-full text-sm">
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.name') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-1 text-left font-semibold">
                                    {{ $submissionRegistrant->full_name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.type') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-1 text-left">
                                    {{ $submissionRegistration->registrationPayment->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.description') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-1 text-left">
                                    {{ $submissionRegistration->registrationPayment->description }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.cost') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-1 text-left">
                                    {{ moneyOrFree($submissionRegistration->registrationPayment->cost, $submissionRegistration->registrationPayment->currency, true) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.registration_date') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-1 text-left">
                                    {{ $submissionRegistration->registrationPayment->created_at->format(App\Facades\Setting::get('format_date')) }}
                                </td>
                            </tr>
                            @if ($submissionRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value && $submissionRegistration->registrationType->currency !== 'free')
                                <tr>
                                    <td class="font-semibold w-fit">{{ __('general.payment_date') }}</td>
                                    <td class="pl-3">:</td>
                                    <td class="py-1 text-left">
                                        {{ $submissionRegistration->registrationPayment->paid_at->format(App\Facades\Setting::get('format_date')) }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="font-semibold w-fit">{{ __('general.status') }}</td>
                                <td class="pl-3">:</td>
                                <td class="py-1 text-left">
                                    @if ($submissionRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value && !$submissionRegistration->trashed())
                                        <x-filament::badge color="success" class="!w-fit">
                                            {{ __('general.paid') }}
                                        </x-filament::badge>
                                    @elseif ($submissionRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Unpaid->value && !$submissionRegistration->trashed())
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
                    @if($isSubmissionAuthor)
                        <x-filament::section>
                            <p>Please do the registration process in order to complete the payment and continue the submission process.</p>
                            <p class="mt-3">You can perform the registration process, by clicking on registration type you preferred below.</p>
                        </x-filament::section>

                        {{-- Author Registration --}}
                        @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\AuthorRegistration::class, ['submission' => $submission])
                    @else
                        <x-filament::section>
                            <p>This submission has not started the registration process yet.</p>
                        </x-filament::section>
                    @endif
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
        <div class="sticky z-30 flex flex-col self-start col-span-4 gap-3 top-24" x-data="{ decision:@js($submissionDecision) }">
            @if($submission->getEditors()->isEmpty() && ! $user->hasRole(\App\Models\Enums\UserRole::ConferenceEditor->value))
                <div class="px-4 py-3.5 text-base text-white rounded-lg border-2 border-primary-700 bg-primary-500">
                    {{ $user->can('assignParticipant', $submission) ? __('general.assign_an_editor_to_enable_the_editorial') : __('general.no_editor_assigned_submission') }}
                </div>
            @else
                @if($submissionDecision)
                    <div class="px-6 py-5 space-y-3 overflow-hidden bg-white shadow-sm rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10">
                        <div class="text-base">
                            {{ $submission->status == SubmissionStatus::Declined ? __('general.submission_declined') : __('general.submission_payment_approved') }}
                        </div>
                        <button class="text-sm underline text-primary-500"
                            @@click="decision = !decision" x-text="decision ? 'Change Decision' : 'Cancel'"
                        ></button>
                    </div>
                @endif

                <div @class([
                    'flex flex-col gap-4 col-span-4',
                    'hidden' => in_array($submission->status, [
                            SubmissionStatus::Queued,
                            SubmissionStatus::Published,
                    ])
                ]) x-show="!decision">
                    @if ($user->can('approvePayment', $submission) && ! in_array($this->submission->status, [SubmissionStatus::OnReview, SubmissionStatus::Editing, SubmissionStatus::OnPresentation]))
                        {{ $this->approvePaymentAction() }}
                    @endif
                    @if ($user->can('declinePayment', $submission) && ! in_array($this->submission->status, [SubmissionStatus::Declined]))
                        {{ $this->declinePaymentAction() }}
                    @endif
                </div>
            @endif

            {{-- Participants --}}
            @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\ParticipantList::class, ['submission' => $submission])

            @if ($isRegistrationOpen)

                {{-- Payment Manual List --}}
                @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\PaymentManualList::class, ['submission' => $submission])

            @endif

        </div>
    </div>
    <x-filament-actions::modals />
</div>
