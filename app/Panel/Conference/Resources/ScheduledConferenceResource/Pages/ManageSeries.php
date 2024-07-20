<?php

namespace App\Panel\Conference\Resources\SerieResource\Pages;

use App\Actions\ScheduledConferences\ScheduledConferenceCreateAction;
use App\Models\Enums\ScheduledConferenceState;
use App\Panel\Conference\Resources\ScheduledConferenceResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;

class ManageSeries extends ManageRecords
{
    protected static string $resource = ScheduledConferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::ExtraLarge)
                ->using(fn(array $data) => ScheduledConferenceCreateAction::run($data)),
        ];
    }

    public function getTabs(): array
    {
        return [
            'current' => Tab::make()
                ->badge(fn () => ScheduledConferenceResource::getEloquentQuery()->where('state', ScheduledConferenceState::Current)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', ScheduledConferenceState::Current)),
            'draft' => Tab::make()
                ->badge(fn () => ScheduledConferenceResource::getEloquentQuery()->where('state', ScheduledConferenceState::Draft)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', ScheduledConferenceState::Draft)),
            'upcoming' => Tab::make()
                ->badge(fn () => ScheduledConferenceResource::getEloquentQuery()->where('state', ScheduledConferenceState::Published)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', ScheduledConferenceState::Published)),
            'archived' => Tab::make()
                ->badge(fn () => ScheduledConferenceResource::getEloquentQuery()->where('state', ScheduledConferenceState::Archived)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', ScheduledConferenceState::Archived)),
        ];
    }
}
