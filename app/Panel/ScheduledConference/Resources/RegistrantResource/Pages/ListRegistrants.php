<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use App\Models\Enums\RegistrationPaymentState;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use App\Models\RegistrationType;
use Filament\Actions;
use Filament\Support\Colors\Color;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use App\Panel\ScheduledConference\Resources\RegistrantResource\Widgets\RegistrationTypeSummary;
use Filament\Navigation\NavigationItem;

class ListRegistrants extends ListRecords
{
    protected static string $resource = RegistrantResource::class;

    protected ?string $heading = 'Registrant';

    public function getSubNavigation(): array
    {
        return static::$resource::getSubNavigation();
    }

    public function getTabs(): array
    {
        return [
            'paid' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->where('trashed', false)
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Paid->value);
                        })
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->where('trashed', false)
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Paid->value);
                        })
                        ->count()
                ),
            'unpaid' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->where('trashed', false)
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Unpaid->value);
                        })
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->where('trashed', false)
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Unpaid->value);
                        })
                        ->count()
                )
                ->badgeColor(Color::Yellow),
            'trash' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->where('trashed', true)
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->where('trashed', true)
                        ->count()
                ),
            'all' => Tab::make()
                ->badge(fn () => Registration::where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())->count()),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
