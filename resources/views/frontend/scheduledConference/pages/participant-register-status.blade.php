@use('Illuminate\Support\Str')
@use('Carbon\Carbon')
@use('App\Models\Enums\RegistrationStatus')
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
                            'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium text-white ring-1 ring-inset ring-gray-500/10', 
                            'bg-green-500' => $userRegistration->getStatus() === RegistrationStatus::Paid->value,
                            'bg-yellow-500' => $userRegistration->getStatus() === RegistrationStatus::Unpaid->value,
                            'bg-red-500' => $userRegistration->getStatus() === RegistrationStatus::Trashed->value,
                        ])>
                            {{ Str::headline(match($userRegistration->getStatus()) {
                                RegistrationStatus::Trashed->value => 'Failed',
                                default => $userRegistration->getStatus(),
                            }) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Type</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        <strong>{{ $userRegistration->registrationType->type }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Description</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        {!! $userRegistration->registrationType->getMeta('description') !!}
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Cost</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        {{ static::formatRegistrationCost($userRegistration) }}
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Registration Date</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        {{ Carbon::parse($userRegistration->created_at)->format('Y-M-d') }}
                    </td>
                </tr>
                @if ($userRegistration->getStatus() === RegistrationStatus::Paid->value && $userRegistration->registrationType->currency !== 'free')
                    <tr>
                        <td class="align-text-top">Payment Date</td>
                        <td class="align-text-top pl-5">:</td>
                        <td class="pl-2">
                            {{ Carbon::parse($userRegistration->paid_at)->format('Y-M-d') }}
                        </td>
                    </tr>
                @endif
            </table>
            @if ($userRegistration->getStatus() === RegistrationStatus::Unpaid->value)
                <hr class="my-8">
                <div class="w-full">
                    @foreach ($paymentList as $currency)
                        <div class="my-6">
                            <div class="flex space-x-4">
                                <h1 class="text-lg font-semibold min-w-fit">
                                    {{ currency($currency->currency)->getName() }} ({{ Str::upper($currency->currency) }})
                                </h1>
                                <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
                            </div>
                            <div class="grid md:grid-cols-6 gap-4 mt-1">
                                @php
                                    $payments = json_decode($currency->payments, true);
                                @endphp
                                @foreach ($payments as $payment)
                                    @if (count($payments) === 1)
                                        <div class="md:col-span-6 px-5 py-3 border border-gray-300 rounded">
                                            <h1 class="font-bold text-left">{{ $payment['name'] }}</h1>
                                            <p class="mt-2">
                                                {{ new Illuminate\Support\HtmlString($payment['detail']) }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="md:col-span-3 px-5 py-3 border border-gray-300 rounded">
                                            <h1 class="font-bold text-left">{{ $payment['name'] }}</h1>
                                            <p class="mt-2">
                                                {{ new Illuminate\Support\HtmlString($payment['detail']) }}
                                            </p>
                                        </div>
                                    @endif
                                @endforeach
                                
                            </div>
                        </div>
                    @endforeach
                    @if ($paymentList->isEmpty())
                        <p>
                            Payment method are empty.
                        </p>
                    @endif
                </div>
            @endif
            @empty(!$currentScheduledConference->getMeta('payment_policy'))
                <hr class="my-8">
                <div class="w-full text-wrap ">
                    <p>
                        {{ new Illuminate\Support\HtmlString($currentScheduledConference->getMeta('payment_policy')) }}
                    </p>
                </div>
            @endempty
        </div>
        @endif
    @else
        {{ abort(404) }}
    @endif
</x-website::layouts.main>