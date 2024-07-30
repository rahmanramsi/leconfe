@use('Illuminate\Support\Str')
@use('Carbon\Carbon')
<x-website::layouts.main>
    <div class="space-y-6">
        <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
    </div>
    @if ($registrationOpen)
        <div class="mt-6 w-full">
            <div class="flex mb-5 space-x-4">
                <h1 class="text-xl font-semibold min-w-fit">Participant Registration</h1>
                <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
            </div>
            @if (!$isSubmit)
                <form wire:submit='register'>
                    <div class="mt-2 w-full">
                        <table class="mt-2 table">
                            <thead class="text-base">
                                <tr>
                                    <td>Registration Type</td>
                                    <td>Quota</td>
                                    <td>Cost</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($registrationTypeList as $index => $type)
                                    @if ($type->active)
                                        @php($elementID = Str::slug($type->type))
                                        <tr @class(['bg-red-100' => $type->isInvalid()])>
                                            <td>
                                                <label>
                                                    <strong>{{ $type->type }}</strong>
                                                    <ul class="text-sm">
                                                        {{ new Illuminate\Support\HtmlString($type->getMeta('description')) }}
                                                    </ul>
                                                </label>
                                            </td>
                                            <td>
                                                <strong>
                                                    @if ($type->isExpired())
                                                        Expired!
                                                    @else
                                                        {{ $type->getPaidParticipantCount() }}/{{ $type->quota }}
                                                    @endif
                                                </strong>
                                            </td>
                                            <td>
                                                <input @class(['cursor-pointer' => !$type->isInvalid()]) id="{{ $elementID }}" type="radio" wire:model="type" value="{{ $type->isInvalid() ? $index : $type->id }}" @disabled($type->isInvalid() || !$isLogged)>
                                                <label @class(['cursor-pointer' => !$type->isInvalid()]) for="{{ $elementID }}">
                                                    {{ ($type->currency === 'free' || $type->cost <= 0) ? 'Free' : '('.Str::upper($type->currency).') '.money($type->cost, $type->currency)}}
                                                </label>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if ($registrationTypeList->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            Registration type are empty.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        @error('type')
                            <div class="text-red-600 text-sm">
                                {{ $message }}
                            </div>
                        @enderror
                        @empty(!$currentScheduledConference->getMeta('registration_policy'))
                            <hr class="my-8">
                            <div class="w-full">
                                {{ new Illuminate\Support\HtmlString($currentScheduledConference->getMeta('registration_policy')) }}
                            </div>
                        @endempty
                        <hr class="my-8">
                        @if ($isLogged)
                            <p class="mb-2">This is your detailed account information.</p>
                            <table class="w-full text-md">
                                <tr>
                                    <td>Name</td>
                                    <td>:</td>
                                    <td>{{ $userModel->full_name }}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>:</td>
                                    <td>{{ $userModel->email }}</td>
                                </tr>
                                <tr>
                                    <td>Affiliation</td>
                                    <td>:</td>
                                    <td>{{ $userModel->getMeta('affiliation', '-') }}</td>
                                </tr>
                                <tr>
                                    <td>Phone</td>
                                    <td>:</td>
                                    <td>{{ $userModel->getMeta('phone', '-') }}</td>
                                </tr>
                                <tr>
                                    <td>Country</td>
                                    <td>:</td>
                                    <td>{{ $userCountry->name }} {{ $userCountry->flag }}</td>
                                </tr>
                            </table>
                            <p class="mt-2">If you feel this is not your account, please log out and use your account.</p>
                        @else
                            <p>
                                You don't have an account, please <x-website::link class="text-blue-600 hover:underline" :href="url('login')">login</x-website::link> first.
                            </p>
                        @endif
                        <hr class="my-8">
                        <div class="flex gap-2 mt-2 justify-end"> 
                            <button type="submit" @class([
                                'btn btn-sm btn-primary',
                                'btn-disabled' => !$isLogged || $registrationTypeList->isEmpty(),
                            ]) x-data x-on:click="window.scrollTo(0, 0)" wire:loading.attr="disabled">
                                <span class="loading loading-spinner loading-xs" wire:loading></span>
                                Register now
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="mt-5">
                    <p class="mt-2">
                        These are your registration details, please double check and confirm if they are correct.
                    </p>
                    <table class="mt-2">
                        <tr>
                            <td class="align-text-top">Type</td>
                            <td class="align-text-top pl-5">:</td>
                            <td class="pl-2">
                                <strong>{{ $registrationType->type }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-text-top">Description</td>
                            <td class="align-text-top pl-5">:</td>
                            <td class="pl-2">
                                {{ new Illuminate\Support\HtmlString($registrationType->getMeta('description')) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-text-top">Cost</td>
                            <td class="align-text-top pl-5">:</td>
                            <td class="pl-2">
                                {{ ($registrationType->cost === 0 || $registrationType->currency === 'free') ? 'Free' : '('.Str::upper($registrationType->currency).') '.money($registrationType->cost, $registrationType->currency) }}
                            </td>
                        </tr>
                    </table>
                    <p class="mt-2">
                        Is this a mistake? You can <button type="button" class="text-red-500 hover:underline" wire:click="cancel" x-data x-on:click="window.scrollTo(0, 0)">cancel</button> this.
                    </p>
                    <hr class="my-8">
                    <p class="mb-2">Please double check your account.</p>
                    <table class="w-full text-md">
                        <tr>
                            <td>Name</td>
                            <td>:</td>
                            <td>{{ $userModel->full_name }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $userModel->email }}</td>
                        </tr>
                        <tr>
                            <td>Affiliation</td>
                            <td>:</td>
                            <td>{{ $userModel->getMeta('affiliation', '-') }}</td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td>{{ $userModel->getMeta('phone', '-') }}</td>
                        </tr>
                        <tr>
                            <td>Country</td>
                            <td>:</td>
                            <td>{{ $userCountry->name }} {{ $userCountry->flag }}</td>
                        </tr>
                    </table>
                    @empty(!$currentScheduledConference->getMeta('payment_policy'))
                        <hr class="my-8">
                        <div class="w-full">
                            <p>
                                {!! $currentScheduledConference->getMeta('payment_policy') !!}
                            </p>
                        </div>
                    @endempty
                    <hr class="my-8">
                    <div class="flex gap-2 mt-2 justify-end">
                        <button type="button" class="btn btn-error text-white btn-sm" wire:click="cancel" x-data x-on:click="window.scrollTo(0, 0)"> 
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" wire:click="confirm" wire:loading.attr="disabled">
                            <span class="loading loading-spinner loading-xs" wire:loading></span>
                            Confirm
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="my-6 w-full">
            <p class="text-lg">
                this conference registration are not open.
            </p>
        </div>
    @endif
</x-website::layouts.main>
                        