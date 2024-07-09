<?php

namespace App\Panel\Series\Resources\TopicResource\Pages;

use App\Panel\Series\Resources\TopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTopics extends ManageRecords
{
    protected static string $resource = TopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('xl'),
        ];
    }
}
