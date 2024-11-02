<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Facades\Hook;
use App\Frontend\Website\Pages\Page;
use App\Models\Registration;
use Illuminate\Support\Facades\Route;
use Rahmanramsi\LivewirePageGroup\PageGroup;

class ParticipantRegisterStatus extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.participant-register-status';

    protected static ?string $slug = 'registration-status';

    public function mount()
    {
        $isLogged = auth()->check();
        $userRegistration = ! $isLogged ? null : Registration::withTrashed()
            ->whereUserId(auth()->user()->id)
            ->first();

        if (! $userRegistration) {
            return redirect(route(ParticipantRegister::getRouteName()));
        }
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $userRegistration = ! $isLogged ? null : Registration::withTrashed()
            ->where('user_id', auth()->user()->id)
            ->first();

        $paymentDetails = [];
        if ($currentScheduledConference->getMeta('manual_payment_instructions')) {
            $paymentDetails[$currentScheduledConference->getMeta('manual_payment_name')] = $currentScheduledConference->getMeta('manual_payment_instructions');
        }

        Hook::call('ParticipantRegisterStatus::PaymentDetails', [$this, $userRegistration, &$paymentDetails]);

        return [
            'currentScheduledConference' => $currentScheduledConference,
            'isLogged' => $isLogged,
            'userRegistration' => $userRegistration,
            'paymentDetails' => $paymentDetails,
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
            ->where('user_id', auth()->user()->id)
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
