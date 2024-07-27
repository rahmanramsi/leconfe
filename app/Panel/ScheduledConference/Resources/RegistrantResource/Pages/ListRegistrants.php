<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use App\Panel\ScheduledConference\Resources\RegistrantResource\Widgets\RegistrationTypeSummary;

class ListRegistrants extends ListRecords
{
    protected static string $resource = RegistrantResource::class;

    protected ?string $heading = 'Registrant';

    protected function getHeaderWidgets(): array
    {
        return [
            RegistrationTypeSummary::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'paid' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('paid_at', '!=', null)->where('is_trashed', false)),
            'unpaid' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('paid_at', '=', null)->where('is_trashed', false)),
            'trash' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_trashed', true)),
            'all' => Tab::make(),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
