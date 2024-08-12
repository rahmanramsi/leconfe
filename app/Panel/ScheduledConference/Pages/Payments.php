<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Tabs;
use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentManuals;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentSettings;
use Illuminate\Contracts\Support\Htmlable;

class Payments extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.payment';

    // protected static ?string $navigationGroup = 'Settings';

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

    // protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 3;

    // protected static ?string $title = 'Payment Methods';

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
                            ->label(__('general.payment'))
                            ->schema([
                                InfolistsVerticalTabs\Tabs::make()
                                    ->schema([
                                        InfolistsVerticalTabs\Tab::make('Manual')
                                            ->label(__('general.manual'))
                                            ->icon('heroicon-o-credit-card')
                                            ->schema([
                                                LivewireEntry::make('manual')
                                                    ->livewire(PaymentManuals::class)
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Settings')
                            ->label(__('general.settings'))
                            ->schema([
                                LivewireEntry::make('settings')
                                    ->livewire(PaymentSettings::class)
                            ])
                    ])
            ]);
    }
}
