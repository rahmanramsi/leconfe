@use('Illuminate\Support\Str')
@use('Carbon\Carbon')
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
                        <span class="
                            inline-flex 
                            items-center 
                            rounded-md 
                            {{ 
                            match ($userRegistration->getStatus()) {
                                'paid' => 'bg-green-500',
                                'unpaid' => 'bg-yellow-500',
                                'trash' => 'bg-red-500',
                            } 
                            }}
                            px-2 py-1 
                            text-xs 
                            font-medium 
                            text-white
                            ring-1 
                            ring-inset 
                            ring-gray-500/10
                        ">
                            {{ Str::headline($userRegistration->getPublicStatus()) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Type</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        <strong>{{ $userRegistration->registration_type->type }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Description</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">
                        {!! $userRegistration->registration_type->getMeta('description') !!}
                    </td>
                </tr>
                <tr>
                    <td class="align-text-top">Cost</td>
                    <td class="align-text-top pl-5">:</td>
                    <td class="pl-2">{{ $userRegistration->registration_type->getCostWithCurrency() }}</td>
                </tr>
                @if ($userRegistration->getStatus() === 'paid' && $userRegistration->registration_type->currency !== 'free')
                    <tr>
                        <td class="align-text-top">Payment Date</td>
                        <td class="align-text-top pl-5">:</td>
                        <td class="pl-2">
                            {{ Carbon::parse($userRegistration->paid_at)->format('Y-M-d') }}
                        </td>
                    </tr>
                @endif
            </table>
            @if ($userRegistration->getStatus() === 'unpaid')
                <hr class="my-8">
                <div class="w-full">
                    @foreach ($paymentList as $currency)
                        <div class="my-6">
                            <div class="flex space-x-4">
                                <h1 class="text-lg font-semibold min-w-fit">{{ currency($currency->currency)->getName() }} ({{ Str::upper($currency->currency) }})</h1>
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
            <hr class="my-8">
            <div class="w-full">
                @if (!empty($currentScheduledConference->getMeta('payment_policy')))
                    <p>{{ new Illuminate\Support\HtmlString($currentScheduledConference->getMeta('payment_policy')) }}</p>
                @else 
                    <p>Payment Policy</p>
                @endif
            </div>
            <hr class="my-8">
        </div>
        @endif
    @else
        {{ abort(404) }}
    @endif
</x-website::layouts.main>