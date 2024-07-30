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
        $userRegistration = !$isLogged ? null : Registration::select('*')
            ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->whereUserId(auth()->user()->id)
            ->first();
        if (!$userRegistration)
            return redirect(route(ParticipantRegister::getRouteName()));
    }

    public static function formatRegistrationCost(Model $record): string
    {
        if ($record->cost === 0 || $record->currency === 'free') {
            return 'Free';
        }
        //---
        $code = Str::upper($record->currency);
        $cost = money($record->cost, $record->currency);
        return "($code) $cost";
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $userRegistration = !$isLogged ? null : Registration::select('*')
            ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->whereUserId(auth()->user()->id)
            ->first();

        $paymentList = PaymentManual::select('*')
            ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
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
