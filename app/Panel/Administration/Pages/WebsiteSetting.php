<?php

namespace App\Panel\Administration\Pages;

use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs;
use App\Panel\Administration\Livewire\LanguageSetting;
use App\Panel\Administration\Livewire\SetupSetting;
use App\Panel\Administration\Livewire\SidebarSetting;
use App\Panel\Administration\Livewire\ThemeSetting;
use App\Panel\Conference\Livewire\NavigationMenuSetting;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class WebsiteSetting extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-m-cog';

    protected static string $view = 'panel.administration.pages.site-settings';

    protected static ?string $navigationGroup = 'Settings';

    public function mount()
    {
    }

    public static function canAccess(): bool
    {
        return Auth::user()->can('update', app()->getSite());
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('site_settings')
                    ->tabs([
                        Tabs\Tab::make('Appearance')
                            ->schema([
                                VerticalTabs\Tabs::make()
                                    ->tabs([
                                        VerticalTabs\Tab::make('Theme')
                                            ->icon('heroicon-o-adjustments-horizontal')
                                            ->schema([
                                                LivewireEntry::make('setup-setting')
                                                    ->livewire(ThemeSetting::class)
                                                    ->lazy(),
                                            ]),
                                        VerticalTabs\Tab::make('Setup')
                                            ->icon('heroicon-o-cog')
                                            ->schema([
                                                LivewireEntry::make('setup-setting')
                                                    ->livewire(SetupSetting::class),
                                            ]),
                                        VerticalTabs\Tab::make('Sidebar')
                                            ->icon('heroicon-o-view-columns')
                                            ->schema([
                                                LivewireEntry::make('sidebar_setting')
                                                    ->livewire(SidebarSetting::class)
                                                    ->lazy(),
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Setup')
                            ->schema([
                                VerticalTabs\Tabs::make()
                                    ->tabs([
                                        VerticalTabs\Tab::make('Navigation Menu')
                                            ->icon('heroicon-o-list-bullet')
                                            ->schema([
                                                LivewireEntry::make('navigation-menu-setting')
                                                    ->livewire(NavigationMenuSetting::class)
                                                    ->lazy(),
                                            ]),
                                        VerticalTabs\Tab::make('Languages')
											->icon('heroicon-o-language')
											->schema([
												LivewireEntry::make('language-setting')
													->livewire(LanguageSetting::class),
											]),
                                    ]),
                            ]),
                    ])
                    ->contained(false),
            ]);
    }
}
