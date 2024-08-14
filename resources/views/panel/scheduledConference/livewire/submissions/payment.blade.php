<div class="space-y-6">
    <div class="grid grid-cols-12 gap-4">
        <div class="space-y-4 col-span-8">
            @if ($userRegistration)
                @if ($userRegistration->registrationPayment->level === App\Models\RegistrationType::LEVEL_AUTHOR)
                    @if ($userRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value)
                        <x-filament::section>
                            <div class="grid grid-cols-11 w-full px-6 py-3 shadow-inner rounded-md">
                                <div class="avatar col-span-1">
                                    <img src="{{ $user->getFilamentAvatarUrl() }}" alt="Profile Picture" class="rounded-full h-min">
                                </div>
                                <div class="col-span-10 inline-block text-sm my-auto">
                                    <strong class="font-semibold">
                                        {{ $user->full_name }}    
                                    </strong>
                                    <p>
                                        Verified Author
                                    </p>
                                </div>
                            </div>
                            <div class="mt-5">
                                <p>
                                    You registered as participant author, you can continue the submission process
                                </p>
                                <p class="mt-3">
                                    Please wait for editor review your submission. 
                                </p>
                            </div>
                        </x-filament::section>
                    @else
                        <x-filament::section>
                            <p>
                                You have not finish the payment process, please finish it and continue your submission process.
                            </p>
                            <p class="mt-3">
                                See your <a class="text-blue-500 hover:underline" href="{{ route('livewirePageGroup.scheduledConference.pages.participant-registration') }}">registration status</a> to get more info about the payment process or check out the manual payment methods on the side.
                            </p>
                        </x-filament::section>
                    @endif
                @else
                    <x-filament::section>
                        <p>
                            You did not register as an author, see your registration status, re-register as an author to continue the submission process.
                        </p>
                    </x-filament::section>
                @endif

                <x-filament::section>
                    <x-slot name="heading">
                        Registration Details
                    </x-slot>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="w-fit font-semibold">Status</td>
                            <td class="pl-3">:</td>
                            <td class="text-left py-2">
                                @if ($userRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value && !$userRegistration->trashed())
                                    <x-filament::badge color="success" class="!w-fit">
                                        Paid
                                    </x-filament::badge>
                                @elseif ($userRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Unpaid->value && !$userRegistration->trashed())
                                    <x-filament::badge color="warning" class="!w-fit">
                                        Unpaid
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="error" class="!w-fit">
                                        Fail
                                    </x-filament::badge>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="w-fit font-semibold">Type</td>
                            <td class="pl-3">:</td>
                            <td class="text-left py-2">
                                {{ $userRegistration->registrationPayment->name }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-fit font-semibold">Description</td>
                            <td class="pl-3">:</td>
                            <td class="text-left py-2">
                                {{ $userRegistration->registrationPayment->description }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-fit font-semibold">Cost</td>
                            <td class="pl-3">:</td>
                            <td class="text-left py-2">
                                {{ money($userRegistration->registrationPayment->cost, $userRegistration->registrationPayment->currency, true) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-fit font-semibold">Registration Date</td>
                            <td class="pl-3">:</td>
                            <td class="text-left py-2">
                                {{ $userRegistration->registrationPayment->created_at->format(App\Facades\Setting::get('format_date')) }}
                            </td>
                        </tr>
                        @if ($userRegistration->registrationPayment->state === App\Models\Enums\RegistrationPaymentState::Paid->value && $userRegistration->registrationType->currency !== 'free')
                            <tr>
                                <td class="w-fit font-semibold">Payment Date</td>
                                <td class="pl-3">:</td>
                                <td class="text-left py-2">
                                    {{ $userRegistration->registrationPayment->paid_at->format(App\Facades\Setting::get('format_date')) }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </x-filament::section>
            @else
                <x-filament::section>
                    <p>
                        You are not participant of this conference, Please start the <a class="text-blue-500 hover:underline" href="{{ route('livewirePageGroup.scheduledConference.pages.participant-registration') }}">registration</a> process, and complete the payment to continue your submission process.
                    </p>
                    <p class="mt-3">
                        Make sure to register as an author, not an participant. 
                    </p>
                </x-filament::section>
            @endif
        </div>
        <div class="space-y-4 col-span-4 gap-2">

            {{-- Payment Manual List --}}
            @livewire(App\Panel\ScheduledConference\Livewire\Submissions\Components\PaymentManualList::class, ['submission' => $submission])

        </div>
    </div>
</div>