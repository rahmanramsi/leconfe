<?php

namespace App\Panel\Conference\Pages;

use App\Models\Enums\UserRole;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use App\Panel\Conference\Widgets;
use Filament\Pages\Dashboard as PagesDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends PagesDashboard
{
    public function mount()
    {
        // $user = Auth::user();
        // if($user->hasAnyRole(static::internalRoles())){
        //     return;
        // }

        // if($user->hasRole('Author')){
        //     return $this->redirect(SubmissionResource::getUrl());
        // }

        // if($user->hasRole(UserRole::Reader->value) || $user->role?->isEmpty()){
        //     return $this->redirectRoute(Profile::getRouteName());
        // }

    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasAnyRole(static::internalRoles()) ? true : false;
    }


    public static function internalRoles(): array
    {
        return [
            UserRole::Admin->value,
            UserRole::ConferenceManager->value,
            UserRole::SeriesManager->value,
            UserRole::ConferenceEditor->value,
        ];
    }

}
