<?php

namespace App\Panel\Administration\Resources\ConferenceResource\Pages;

use App\Actions\Conferences\ConferenceCreateAction;
use App\Panel\Administration\Resources\ConferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListConferences extends ListRecords
{
    protected static string $resource = ConferenceResource::class;

    public int $upcomingConferenceCount = 0;

    public int $archivedConferenceCount = 0;

    public function mount(): void
    {
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::ExtraLarge)
                ->using(function (array $data) {
                    $record = ConferenceCreateAction::run($data);

                    return $record;
                }),
        ];
    }
}
