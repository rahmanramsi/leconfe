@use('Illuminate\Support\Str')
@use('Carbon\Carbon')
<x-website::layouts.main>
    <div class="space-y-6">
        <x-website::breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
    </div>
    @if ($registrationOpen)
        <div class="w-full mt-6">
            <div class="flex mb-5 space-x-4">
                <h1 class="text-xl font-semibold min-w-fit">{{ __('general.participant_registration') }}</h1>
                <hr class="w-full h-px my-auto bg-gray-200 border-0">
            </div>
            @if (!$isSubmit)
                <form wire:submit='register'>
                    <div class="w-full mt-2">
                        <div class="mt-2 overflow-x-auto">
                            <table class="table mt-2 table-xs sm:table-md">
                                <thead class="text-base">
                                    <tr>
                                        <td>{{ __('general.registration_type') }}</td>
                                        <td>{{ __('general.quota') }}</td>
                                        <td>{{ __('general.level') }}</td>
                                        <td>{{ __('general.cost') }}</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($registrationTypeList as $index => $type)
                                        @if ($type->active)
                                            <tr>
                                                <td>
                                                    <strong>{{ $type->type }}</strong>
                                                    <p class="text-xs sm:text-sm">
                                                        {{ $type->getMeta('description') }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <strong>
                                                        {{ $type->getPaidParticipantCount() }}/{{ $type->quota }}
                                                    </strong>
                                                    <p class="text-xs sm:test-sm">
                                                        {{ $type->isOpen() ? null : __('general.closed') }}
                                                    </p>
                                                </td>
                                                <td>
                                                    {{ 
                                                        match ($type->level) {
                                                            App\Models\RegistrationType::LEVEL_PARTICIPANT => 'Participant',
                                                            App\Models\RegistrationType::LEVEL_AUTHOR => 'Author',
                                                            default => 'None',
                                                        }    
                                                    }}
                                                </td>
                                                <td>
                                                    @php
                                                        $typeCostFormatted = moneyOrFree($type->cost, $type->currency, true);
                                                        $elementID = Str::slug($type->type)
                                                    @endphp
                                                    <div class="flex items-center gap-2">
                                                        @if($isLogged)
                                                            @if ($type->level !== App\Models\RegistrationType::LEVEL_AUTHOR)
                                                                <input class="radio radio-xs radio-primary mr-1" id="{{ $elementID }}" type="radio" wire:model="type" value="{{ $type->id }}" @disabled(!$type->isOpen())>
                                                            @else
                                                                <span class="tooltip" data-tip="This registration type is for information only">
                                                                    <x-filament::icon
                                                                        icon="heroicon-o-question-mark-circle"
                                                                        class="h-5 w-5 text-gray-500 dark:text-gray-400"
                                                                    />
                                                                </span>
                                                            @endif
                                                        @endif
                                                        <label @class(['cursor-pointer' => $type->isOpen() && $type->level !== App\Models\RegistrationType::LEVEL_AUTHOR]) for="{{ $elementID }}">
                                                            {{ $typeCostFormatted }}
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @if ($registrationTypeList->isEmpty())
                                        <tr>
                                            <td colspan="3" class="text-center">
                                                {{ __('general.registration_type_are_empty') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        @error('type')
                            <div class="text-sm text-red-600">
                                {{ $message }}
                            </div>
                        @enderror
                        @empty(!$currentScheduledConference->getMeta('registration_policy'))
                            <hr class="my-8">
                            <div class="w-full user-content">
                                {!! new Illuminate\Support\HtmlString($currentScheduledConference->getMeta('registration_policy')) !!}
                            </div>
                        @endempty
                        <hr class="my-8">
                        @if ($isLogged)
                            <p class="mb-2 font-medium">{{ __('general.this_is_your_detailed_account_information') }}</p>
                            <table class="w-full text-md">
                                <tr>
                                    <td>{{ __('general.name') }}</td>
                                    <td>:</td>
                                    <td>{{ $userModel->full_name }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('general.email') }}</td>
                                    <td>:</td>
                                    <td>{{ $userModel->email }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('general.affiliation') }}</td>
                                    <td>:</td>
                                    <td>{{ $userModel->getMeta('affiliation') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('general.phone') }}</td>
                                    <td>:</td>
                                    <td>{{ $userModel->getMeta('phone',) ?? '-'  }}</td>
                                </tr>
                                @if($userCountry)
                                    <tr>
                                        <td>{{ __('general.country') }}</td>
                                        <td>:</td>
                                        <td>{{ $userCountry->flag . ' ' . $userCountry->name }}</td>
                                    </tr>
                                @endif
                            </table>
                            <p class="mt-2">{{ __('general.if_you_feel_this_is_not_your_account_please_log_out_and_use_your_account') }}</p>
                        @else
                            <p>
                                {!! __('general.currently_not_logged_in', ['url' => app()->getLoginUrl() ]) !!}
                            </p>
                        @endif
                        <hr class="my-8">
                        <div class="flex justify-end gap-2 mt-2">
                            <button type="submit" @class([
                                'btn btn-sm btn-primary',
                                'btn-disabled' => !$isLogged || $registrationTypeList->isEmpty(),
                            ]) x-data x-on:click="window.scrollTo(0, 0)" wire:loading.attr="disabled">
                                <span class="loading loading-spinner loading-xs" wire:loading></span>
                                {{ __('general.register_now') }}
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="mt-5">
                    <p class="mt-2">
                        {{ __('general.these_are_your_registration_details') }}
                    </p>
                    <div class="mt-2 overflow-x-auto">
                        <table>
                            <tr>
                                <td class="align-text-top">{{ __('general.type') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    <strong>{{ $registrationType->type }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-text-top">{{ __('general.description') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    {{ $registrationType->getMeta('description') ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="align-text-top">{{ __('general.cost') }}</td>
                                <td class="pl-5 align-text-top">:</td>
                                <td class="pl-2">
                                    {{ moneyOrFree($registrationType->cost, $registrationType->currency, true) }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <p class="mt-2">
                        {!! __('general.is_mistake_you_can_cancel') !!}
                    </p>
                    <hr class="my-8">
                    <p class="mb-2">{{ __('general.please_double_check_your_account') }}</p>
                    <table class="w-full text-md">
                        <tr>
                            <td>{{ __('general.name') }}</td>
                            <td>:</td>
                            <td>{{ $userModel->full_name }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('general.email') }}</td>
                            <td>:</td>
                            <td>{{ $userModel->email }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('general.affiliation') }}</td>
                            <td>:</td>
                            <td>{{ $userModel->getMeta('affiliation') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('general.phone') }}</td>
                            <td>:</td>
                            <td>{{ $userModel->getMeta('phone') ?? '-' }}</td>
                        </tr>
                        @if($userCountry)
                            <tr>
                                <td>{{ __('general.country') }}</td>
                                <td>:</td>
                                <td>{{ $userCountry->flag . ' ' . $userCountry->name }}</td>
                            </tr>
                        @endif
                    </table>
                    @empty(!$currentScheduledConference->getMeta('payment_policy'))
                        <hr class="my-8">
                        <div class="w-full user-content">
                            <p>
                                {!! $currentScheduledConference->getMeta('payment_policy') !!}
                            </p>
                        </div>
                    @endempty
                    <hr class="my-8">
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" class="text-white btn btn-error btn-sm" wire:click="cancel" x-data x-on:click="window.scrollTo(0, 0)">
                            {{ __('general.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" wire:click="confirm" wire:loading.attr="disabled">
                            <span class="loading loading-spinner loading-xs" wire:loading></span>
                            {{ __('general.confirm') }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="w-full my-6">
            <p class="text-lg">
                {{ __('general.registration_are_closed') }}
            </p>
        </div>
    @endif
</x-website::layouts.main>
