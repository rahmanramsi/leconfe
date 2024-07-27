<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

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
                ->modifyQueryUsing(fn (Builder $query) => $query->where('paid_at', '!=', null)->where('is_trashed', false))
                ->badge(fn () => Registration::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->where('paid_at', '!=', null)->where('is_trashed', false)->count())
                ->badgeColor(Color::Green),
            'unpaid' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('paid_at', '=', null)->where('is_trashed', false))
                ->badge(fn () => Registration::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->where('paid_at', '=', null)->where('is_trashed', false)->count())
                ->badgeColor(Color::Yellow),
            'trash' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_trashed', true))
                ->badge(fn () => Registration::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->where('is_trashed', true)->count())
                ->badgeColor(Color::Red),
            'all' => Tab::make()
                ->badge(fn () => Registration::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->count()),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
