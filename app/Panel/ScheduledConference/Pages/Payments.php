<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\Payment\ManualPaymentPage;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\SubNavigationPosition;

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
                InfolistsVerticalTabs\Tabs::make()
                    ->schema([
                        InfolistsVerticalTabs\Tab::make('Manual')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                LivewireEntry::make('manual')
                                    ->livewire(ManualPaymentPage::class)
                            ]),
                    ]),
            ]);
    }

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public static function getPaymentSubNavigation(): array
    {
        return [
            NavigationGroup::make()
                ->items([
                    NavigationItem::make('payment')
                        ->label('Payment')
                        ->isActiveWhen(fn () => Payments::getUrl() === url()->current())
                        ->url(fn () => Payments::getUrl()),
                    NavigationItem::make('payment')
                        ->label('Settings')
                        ->isActiveWhen(fn () => PaymentSettings::getUrl() === url()->current())
                        ->url(fn () => PaymentSettings::getUrl()),
                ])
        ];
    }

    public function getSubNavigation(): array
    {
        return Payments::getPaymentSubNavigation();
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getNavigationItemActiveRoutePattern()) || request()->routeIs(PaymentSettings::getNavigationItemActiveRoutePattern()))
                ->sort(static::getNavigationSort())
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->badgeTooltip(static::getNavigationBadgeTooltip())
                ->url(static::getNavigationUrl()),
        ];
    }
}
