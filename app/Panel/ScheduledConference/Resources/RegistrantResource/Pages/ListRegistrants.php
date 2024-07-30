<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use App\Models\Enums\RegistrationStatus;
use App\Models\Registration;
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
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', RegistrationStatus::Paid->value)->where('trashed', false))
                ->badge(fn () => Registration::select('id')
                    ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
                    ->where('state', RegistrationStatus::Paid->value)
                    ->where('trashed', false)
                    ->count()
                ),
            'unpaid' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', RegistrationStatus::Unpaid->value)->where('trashed', false))
                ->badge(fn () => Registration::select('id')
                    ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
                    ->where('state', RegistrationStatus::Unpaid->value)
                    ->where('trashed', false)
                    ->count()
                )
                ->badgeColor(Color::Yellow),
            'trashed' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('trashed', true))
                ->badge(fn () => Registration::select('id')
                    ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
                    ->where('trashed', true)
                    ->count()
                ),
            'all' => Tab::make()
                ->badge(fn () => Registration::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->count()),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
