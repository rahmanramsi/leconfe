<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use App\Models\Enums\RegistrationPaymentState;
use App\Panel\ScheduledConference\Pages\Registrations;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use Filament\Actions\Action;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class ListRegistrants extends ListRecords
{
    protected static string $resource = RegistrantResource::class;

    protected ?string $heading = 'Registrants';

    public function getHeaderActions(): array
    {
        return [
            Action::make('settings')
                ->color('gray')
                ->icon('heroicon-o-cog-6-tooth')
                ->outlined()
                ->url(Registrations::getUrl()),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('general.all'))
                ->badge(fn () => static::$resource::getEloquentQuery()
                    ->count()
                ),
            'paid' => Tab::make()
                ->label(__('general.paid'))
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->WhereNull('deleted_at')
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Paid->value);
                        })
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->WhereNull('deleted_at')
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Paid->value);
                        })
                        ->count()
                ),
            'unpaid' => Tab::make()
                ->label(__('general.unpaid'))
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->WhereNull('deleted_at')
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Unpaid->value);
                        })
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->WhereNull('deleted_at')
                        ->whereHas('registrationPayment', function ($query) {
                            $query->where('state', RegistrationPaymentState::Unpaid->value);
                        })
                        ->count()
                )
                ->badgeColor(Color::Yellow),
            'trash' => Tab::make()
                ->label(__('general.trash'))
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->whereNotNull('deleted_at')
                )
                ->badge(
                    fn () => static::$resource::getEloquentQuery()
                        ->whereNotNull('deleted_at')
                        ->count()
                ),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
