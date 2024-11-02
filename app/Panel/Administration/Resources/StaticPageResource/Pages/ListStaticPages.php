<?php

namespace App\Panel\Administration\Resources\StaticPageResource\Pages;

use App\Actions\StaticPages\StaticPageCreateAction;
use App\Panel\Administration\Resources\StaticPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaticPages extends ListRecords
{
    protected static string $resource = StaticPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(fn (array $data) => StaticPageCreateAction::run($data)),
        ];
    }
}
