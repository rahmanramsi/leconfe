<?php

namespace App\Frontend\ScheduledConference\Pages;

use Illuminate\Support\Str;
use App\Models\Registration;
use App\Models\PaymentManual;
use Livewire\Attributes\Title;
use App\Models\RegistrationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class ParticipantRegisterStatus extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.participant-register-status';

    protected static ?string $slug = 'registration-status';

    public function mount()
    {
        $isLogged = auth()->check();
        $userRegistration = !$isLogged ? null : Registration::withTrashed()
            ->whereUserId(auth()->user()->id)
            ->first();

        if (!$userRegistration)
            return redirect(route(ParticipantRegister::getRouteName()));
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $userRegistration = !$isLogged ? null : Registration::withTrashed()
            ->where('user_id', auth()->user()->id)
            ->first();

        $paymentList = [];
        $payments = PaymentManual::select('*')->get();

        foreach ($payments as $payment) {
            $paymentCurrencyCode = $payment->currency;
            if(!isset($paymentList[$paymentCurrencyCode])) {
                $paymentList[$paymentCurrencyCode] = [];
            }
            $paymentList[$paymentCurrencyCode][] = $payment;
        }

        return [
            'currentScheduledConference' => $currentScheduledConference,
            'isLogged' => $isLogged,
            'userRegistration' => $userRegistration,
            'paymentList' => $paymentList,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => __('general.home'),
            __('general.registration_status'),
        ];
    }

    public function cancel()
    {
        Registration::withTrashed()
            ->whereUserId(auth()->user()->id)
            ->first()
            ->forceDelete();

        return redirect(route(ParticipantRegister::getRouteName()));
    }

    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();
        Route::get("/{$slug}", static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }
}
