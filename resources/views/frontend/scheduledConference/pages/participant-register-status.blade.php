@use('App\Models\Enums\RegistrationPaymentState')
<x-website::layouts.main>
    @if ($isLogged)
        <div class="space-y-6">
            <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
        </div>
        @if ($userRegistration)
            <div class="mt-5">
                <div class="flex mb-5 space-x-4">
                    <h1 class="text-xl font-semibold min-w-fit">{{ __('general.registration_status') }}</h1>
                    <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
                </div>
                <div class="divide-y *:py-6">
                    <div class="first:pt-0">
                        <p class="mt-2">{{ __('general.this_your_pacticipant_retistration_details') }}</p>
                        <table class="mt-2">
                            <tr>
                                <td class="align-text-top">{{ __('general.status') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    <span @class([
                                        'badge',
                                        'badge-success' => $userRegistration->getState() === RegistrationPaymentState::Paid->value,
                                        'badge-warning' => $userRegistration->getState() === RegistrationPaymentState::Unpaid->value,
                                        '!badge-error' => $userRegistration->trashed(),
                                    ])>
                                        {{
                                            $userRegistration->trashed() ?  'Failed' : $userRegistration->getState()
                                        }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-text-top">{{ __('general.type') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    <strong>{{ $userRegistration->registrationPayment->name }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-text-top">{{ __('general.level') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    {{ 
                                        match ($userRegistration->registrationPayment->level) {
                                            App\Models\RegistrationType::LEVEL_PARTICIPANT => 'Participant',
                                            App\Models\RegistrationType::LEVEL_AUTHOR => 'Author',
                                            default => 'None',
                                        }    
                                    }}
                                </td>
                            </tr>
                            <tr>
                                <td class="align-text-top">{{ __('general.description') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    {!! $userRegistration->registrationPayment->description !!}
                                </td>
                            </tr>
                            <tr>
                                <td class="align-text-top">{{ __('general.cost') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    @php
                                        $userRegistrationCostFormatted = moneyOrFree($userRegistration->registrationPayment->cost, $userRegistration->registrationPayment->currency, true);
                                    @endphp
                                    {{ $userRegistrationCostFormatted }}
                                </td>
                            </tr>
                            <tr>
                                <td class="align-text-top">{{ __('general.registration_date') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    {{ $userRegistration->created_at->format(Setting::get('format_date')) }}
                                </td>
                            </tr>
                            @if ($userRegistration->getState() === RegistrationPaymentState::Paid->value && $userRegistration->registrationType->currency !== 'free')
                                <tr>
                                    <td class="align-text-top">{{ __('general.payment_date') }}</td>
                                    <td class="pl-5 align-text-top">:</td>
                                    <td class="pl-2">
                                        {{ $userRegistration->registrationPayment->paid_at->format(Setting::get('format_date')) }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                        @if($userRegistration->getState() === RegistrationPaymentState::Paid->value && $userRegistration->registrationPayment->level === App\Models\RegistrationType::LEVEL_AUTHOR && !$userRegistration->trashed())
                            <div class="mt-4">
                                <a class="btn btn-success btn-sm" href="{{ App\Panel\ScheduledConference\Resources\SubmissionResource::getUrl('index', panel: App\Providers\PanelProvider::PANEL_SCHEDULED_CONFERENCE) }}">
                                    {{ __('general.submission') }}
                                </a>
                            </div>
                        @elseif($userRegistration->getState() === RegistrationPaymentState::Unpaid->value || $userRegistration->trashed())
                            <div class="mt-4" x-data="{ isCancelling: false }">
                                <button class="btn btn-error btn-sm" x-show="!isCancelling" x-on:click="isCancelling = true"> {{ __('general.cancel_registration') }}</button>
                                <div class="space-y-2" x-show="isCancelling" x-cloak>
                                    <p class="mr-2">{{ __('general.are_you_sure_want_to_cancel_registration') }}</p>
                                    <div class="flex items-center gap-2">
                                        <button class="btn btn-sm btn-outline" x-on:click="isCancelling = false">{{ __('general.no') }}</button>
                                        <button class="btn btn-error btn-sm" wire:click="cancel">{{ __('general.yes') }}</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    @if ($userRegistration->getState() === RegistrationPaymentState::Unpaid->value && !$userRegistration->trashed() && !empty($paymentDetails))
                        <div class="payment-details join join-vertical w-full">
                            @foreach($paymentDetails as $paymentTitle => $paymentDescription)
                                <div class="payment-detail collapse collapse-arrow join-item border-base-300 border">
                                    <input type="radio" name="my-accordion-4" @if($loop->first) checked="checked" @endif />
                                    <div class="collapse-title text-lg font-medium">{{ $paymentTitle }}</div>
                                    <div class="collapse-content user-content">
                                        {!! new Illuminate\Support\HtmlString($paymentDescription) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($currentScheduledConference->getMeta('payment_policy')))
                        <div class="payment-policy">
                            <h2 class="text-lg font-medium mb-6">{{ __('general.payment_policy') }}</h2>
                            <div class="w-full text-wrap user-content">
                                {!! new Illuminate\Support\HtmlString($currentScheduledConference->getMeta('payment_policy')) !!}
                            </div>
                        </div>
                    @endif
                    @if(!empty($currentScheduledConference->getMeta('registration_policy')))
                        <div class="registration-policy">
                            <h2 class="text-lg font-medium mb-6">{{ __('general.registration_policy') }}</h2>
                            <div class="w-full text-wrap user-content">
                                {!! new Illuminate\Support\HtmlString($currentScheduledConference->getMeta('registration_policy')) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @else
        {{ abort(404) }}
    @endif
</x-website::layouts.main>
