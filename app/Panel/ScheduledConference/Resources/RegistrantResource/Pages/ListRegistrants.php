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

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () => static::$resource::getEloquentQuery()
                    ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
                    ->count()
                ),
            'paid' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->WhereNull('deleted_at')  
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Paid->value);
                        })
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
                        ->WhereNull('deleted_at')  
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Paid->value);
                        })
                        ->count()
                ),
            'unpaid' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->WhereNull('deleted_at') 
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Unpaid->value);
                        })
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
                        ->WhereNull('deleted_at')    
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Unpaid->value);
                        })
                        ->count()
                )
                ->badgeColor(Color::Yellow),
            'trash' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->whereNotNull('deleted_at')
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
                        ->whereNotNull('deleted_at')    
                        ->count()
                ),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
