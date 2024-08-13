@use('App\Models\Enums\RegistrationPaymentState')
<x-website::layouts.main>
    @if ($isLogged)
        <div class="space-y-6">
            <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
        </div>
        @if ($userRegistration)
        <div class="mt-5">
            <div class="flex mb-5 space-x-4">
                <h1 class="text-xl font-semibold min-w-fit">Registration Status</h1>
                <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
            </div>
            <p class="mt-2">This is your participant registration details.</p>
            <table class="mt-2">
                <tr>
                    <td class="align-text-top">Status</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        <span @class([
                            'badge', 
                            'badge-success' => $userRegistration->getState() === RegistrationPaymentState::Paid->value,
                            'badge-warning' => $userRegistration->getState() === RegistrationPaymentState::Unpaid->value,
                            'badge-error' => $userRegistration->trashed(),
                        ])>
                            {{ 
                                $userRegistration->trashed() ?  'Failed' : $userRegistration->getState()
                            }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Type</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        <strong>{{ $userRegistration->registrationPayment->name }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Description</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        {!! $userRegistration->registrationPayment->description !!}
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Cost</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        @php
                            $userRegistrationCost = $userRegistration->registrationPayment->cost;
                            $userRegistrationCurrency = Str::upper($userRegistration->registrationPayment->currency);
                            $userRegistrationCostFormatted = money($userRegistrationCost, $userRegistrationCurrency, true);
                        @endphp
                        {{ 
                            ($userRegistrationCost === 0 || $userRegistrationCurrency === 'FREE') ? 
                            'Free' : "$userRegistrationCostFormatted"
                        }}
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Registration Date</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        {{ $userRegistration->created_at->format(Setting::get('format_date')) }}
                    </td>
                </tr>
                @if ($userRegistration->getState() === RegistrationPaymentState::Paid->value && $userRegistration->registrationType->currency !== 'free')
                    <tr>
                        <td class="align-text-top">Payment Date</td>
                        <td class="align-text-top pl-5">:</td>
                        <td class="pl-2">
                            {{ $userRegistration->registrationPayment->paid_at->format(Setting::get('format_date')) }}
                        </td>
                    </tr>
                @endif
            </table>
            @if($userRegistration->getState() === RegistrationPaymentState::Unpaid->value)
            <div class="mt-4" x-data="{ isCancelling: false }">
                <button class="btn btn-error btn-sm" x-show="!isCancelling" x-on:click="isCancelling = true">Cancel Registration</button>
                <div class="space-y-2" x-show="isCancelling" x-cloak>
                    <p class="mr-2">Are you sure you want to cancel your registration?</p>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-sm btn-outline" x-on:click="isCancelling = false">No</button>
                        <button class="btn btn-error btn-sm" wire:click="cancel">Yes</button>
                    </div>
                </div>
            </div>
            @endif
            @if ($userRegistration->getState() === RegistrationPaymentState::Unpaid->value && !$userRegistration->trashed())
                <hr class="my-8">
                <div class="w-full">
                    @foreach ($paymentList as $currency => $payments)
                        <div class="my-6">
                            <div class="flex space-x-4">
                                <h1 class="text-lg font-semibold min-w-fit">
                                    {{ currency($currency)->getName() }} ({{ Str::upper($currency) }})
                                </h1>
                                <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
                            </div>
                            <div class="grid md:grid-cols-6 gap-4 mt-1">
                                @foreach ($payments as $payment)
                                    <div @class([
                                        'px-5 py-3 border border-gray-300 rounded',
                                        'md:col-span-6' => count($payments) === 1,
                                        'md:col-span-3' => count($payments) !== 1,
                                    ])>
                                        <h1 class="font-bold text-left">{{ $payment->name }}</h1>
                                        <div class="user-content">
                                            {{ new Illuminate\Support\HtmlString($payment->detail) }}
                                        </div>
                                    </div>
                                @endforeach
                                
                            </div>
                        </div>
                    @endforeach
                    @if (!count($paymentList))
                        <p>
                            Payment method are empty.
                        </p>
                    @endif
                </div>
            @endif
            @if(!empty($currentScheduledConference->getMeta('payment_policy')))
                <hr class="my-8">
                <div class="w-full text-wrap ">
                    <p>
                        {{ new Illuminate\Support\HtmlString($currentScheduledConference->getMeta('payment_policy')) }}
                    </p>
                </div>
            @endif
        </div>
        @endif
    @else
        {{ abort(404) }}
    @endif
</x-website::layouts.main>