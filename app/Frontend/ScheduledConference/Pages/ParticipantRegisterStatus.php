<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\PaymentManual;
use App\Models\Registration;
use Livewire\Attributes\Title;
use App\Models\RegistrationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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
        $userRegistration = !$isLogged ? null : Registration::select('*')
            ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
            ->whereUserId(auth()->user()->id)
            ->first();
        if (!$userRegistration)
            return redirect(route(ParticipantRegister::getRouteName()));
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $userRegistration = !$isLogged ? null : Registration::select('*')
            ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
            ->whereUserId(auth()->user()->id)
            ->first();

        $paymentList = PaymentManual::select('*')
            ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
            ->select(
                'currency',
                DB::raw('JSON_ARRAYAGG(JSON_OBJECT(
                    "name", name,
                    "currency", currency,
                    "detail", detail
                )) AS payments')
            )
            ->groupBy('currency')
            ->get();
        
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
            route(Home::getRouteName()) => 'Home',
            'Registration Status',
        ];
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
