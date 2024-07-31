<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Tabs;
use App\Infolists\Components\LivewireEntry;
use App\Panel\ScheduledConference\Pages\PaymentManualPage;
use App\Panel\ScheduledConference\Pages\PaymentSettingPage;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;

class Payments extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.payment';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payment';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Payment Method';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if($user->can('RegistrationSetting:viewAny')) {
            return true;
        }
        return false;
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
                                                    ->livewire(PaymentManualPage::class)
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Settings')
                            ->schema([
                                LivewireEntry::make('settings')
                                    ->livewire(PaymentSettingPage::class)
                            ])
                    ])
            ]);
    }
}
