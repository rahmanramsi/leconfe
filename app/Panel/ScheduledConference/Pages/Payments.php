<?php

namespace App\Panel\ScheduledConference\Pages;

use App\Facades\Hook;
use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\Payment\ManualPaymentSetting;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentSetting;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Payments extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.payment';

    public function mount() {}

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
        $paymentTabs = [
            InfolistsVerticalTabs\Tab::make('Settings')
                ->label(__('general.settings'))
                ->icon('heroicon-o-cog')
                ->schema([
                    LivewireEntry::make('settings')
                        ->livewire(PaymentSetting::class),
                ]),
            InfolistsVerticalTabs\Tab::make('Manual')
                ->label(__('general.manual'))
                ->icon('heroicon-o-credit-card')
                ->schema([
                    LivewireEntry::make('manual')
                        ->livewire(ManualPaymentSetting::class),
                ]),
        ];

        Hook::call('Payments::PaymentTab', [&$paymentTabs, $this]);

        return $infolist
            ->schema([
                InfolistsVerticalTabs\Tabs::make()
                    ->schema($paymentTabs),
            ]);
    }
}
