<?php

namespace App\Panel\ScheduledConference\Resources\TimelineResource\Pages;

use App\Models\Session;
use App\Models\Timeline;
use App\Panel\ScheduledConference\Resources\TimelineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ManageTimeline extends ListRecords
{
    protected static string $resource = TimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Sessions')
                ->color('gray')
                ->url(fn () => static::$resource::getUrl('all-session'))
                ->authorize('viewAny', Session::class),
            Actions\CreateAction::make()
                ->modalHeading(__('general.add_timeline'))
                ->modalWidth(MaxWidth::ExtraLarge)
                ->model(Timeline::class)
                ->authorize('create', Timeline::class),
        ];
    }
}
