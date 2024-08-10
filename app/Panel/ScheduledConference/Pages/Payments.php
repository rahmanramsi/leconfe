<?php

namespace App\Panel\ScheduledConference\Pages;  

use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Tabs;
use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentManualTable;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentSetting;

class Payments extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.payment';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Payment Methods';

    public static function canAccess(): bool
    {
        return auth()->user()->can('PaymentSetting:viewAny');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make()
                    ->contained(false)
                    ->schema([
                        Tabs\Tab::make('Payment')
                            ->schema([
                                InfolistsVerticalTabs\Tabs::make()
                                    ->schema([
                                        InfolistsVerticalTabs\Tab::make('Manual')
                                            ->icon('heroicon-o-credit-card')
                                            ->schema([
                                                LivewireEntry::make('manual')
                                                    ->livewire(PaymentManualTable::class)
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Settings')
                            ->schema([
                                LivewireEntry::make('settings')
                                    ->livewire(PaymentSetting::class)
                            ])
                    ])
            ]);
    }
}
