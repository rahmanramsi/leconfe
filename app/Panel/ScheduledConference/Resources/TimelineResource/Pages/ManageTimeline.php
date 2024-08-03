<?php

namespace App\Panel\ScheduledConference\Resources\TimelineResource\Pages;

use Filament\Actions;
use Filament\Support\Enums\MaxWidth;
use Filament\Resources\Pages\ListRecords;
use App\Panel\ScheduledConference\Resources\TimelineResource;

class ManageTimeline extends ListRecords
{
    protected static string $resource = TimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Add Timeline')
                ->modalWidth(MaxWidth::ExtraLarge),
        ];
    }
}
