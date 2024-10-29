<?php

namespace App\Panel\Administration\Pages;

use App\Infolists\Components\LivewireEntry;
use App\Panel\Administration\Livewire\PluginGalleryTable;
use App\Panel\Administration\Livewire\PluginTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Infolists\Components\Tabs;

class PluginManagement extends Page implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static string $view = 'panel.administration.pages.plugin-management';

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): string
    {
        return __('general.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.plugin');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Installed Plugins')
                            ->schema([
                                LivewireEntry::make('plugin-installed')
                                    ->livewire(PluginTable::class),
                            ]),
                        Tabs\Tab::make('Plugin Gallery')
                            ->schema([
                                LivewireEntry::make('plugin-gallery')
                                    ->livewire(PluginGalleryTable::class)
                                    ->lazy(),
                            ]),
                    ])
                    ->contained(false)
            ]);
    }
}
