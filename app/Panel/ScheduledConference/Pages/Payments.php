<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Tabs;
use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentManualTable;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentSetting;
use Illuminate\Contracts\Support\Htmlable;

class Payments extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.payment';


    public static function getNavigationGroup(): string
    {
        return __('general.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.payments');
    }

    public function getHeading(): string|Htmlable
    {
        return __('general.payment_methods');
    }

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 3;


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
                            ->label(__('general.payment'))
                            ->schema([
                                InfolistsVerticalTabs\Tabs::make()
                                    ->schema([
                                        InfolistsVerticalTabs\Tab::make('Manual')
                                            ->label(__('general.manual'))
                                            ->icon('heroicon-o-credit-card')
                                            ->schema([
                                                LivewireEntry::make('manual')
                                                    ->livewire(PaymentManualTable::class)
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Settings')
                            ->label(__('general.settings'))
                            ->schema([
                                LivewireEntry::make('settings')
                                    ->livewire(PaymentSetting::class)
                            ])
                    ])
            ]);
    }
}
