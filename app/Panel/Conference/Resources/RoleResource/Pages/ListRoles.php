<?php

namespace App\Panel\Conference\Resources\RoleResource\Pages;

use App\Panel\Conference\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    // protected static ?string $title = 'Role Management';

    public function getHeading(): string|Htmlable
    {
        return __('general.role_management');
    }



    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
