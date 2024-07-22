<?php

namespace App\Panel\Conference\Pages;

use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Components\Tabs;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Infolists\Components\LivewireEntry;
use App\Panel\Administration\Livewire\SidebarSetting;
use App\Panel\Conference\Livewire\NavigationMenuSetting;
use App\Panel\Conference\Livewire\SetupSetting;
use App\Panel\Conference\Livewire\ThemeSetting;

class WebsiteSetting extends Page
{
	protected static string $view = 'panel.conference.pages.website-setting';

	protected static ?string $navigationGroup = 'Settings';

	protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

	protected static ?string $navigationLabel = 'Website';

	public function mount(): void
	{
		$this->authorize('update', App::getCurrentConference());
	}

	public static function shouldRegisterNavigation(): bool
	{
		return Auth::user()->can('update', App::getCurrentConference());
	}

	public function infolist(Infolist $infolist): Infolist
	{
		return $infolist
			->schema([
				Tabs::make()
					->contained(false)
					->tabs([
						Tabs\Tab::make('Appearance')
							->schema([
								InfolistsVerticalTabs\Tabs::make()
									->schema([
										InfolistsVerticalTabs\Tab::make('Theme')
											->icon('heroicon-o-adjustments-horizontal')
											->schema([
												LivewireEntry::make('setup-setting')
													->livewire(ThemeSetting::class),
											]),
										InfolistsVerticalTabs\Tab::make('Setup')
											->icon('heroicon-o-cog')
											->schema([
												LivewireEntry::make('setup-setting')
													->livewire(SetupSetting::class),
											]),
										InfolistsVerticalTabs\Tab::make('Sidebar')
											->icon('heroicon-o-view-columns')
											->schema([
												LivewireEntry::make('sidebar-setting')
													->livewire(SidebarSetting::class),
											]),
									]),
							]),
						Tabs\Tab::make('Setup')
							->schema([
								InfolistsVerticalTabs\Tabs::make()
									->schema([
										InfolistsVerticalTabs\Tab::make('Navigation Menu')
											->icon('heroicon-o-list-bullet')
											->schema([
												LivewireEntry::make('navigation-menu-setting')
													->livewire(NavigationMenuSetting::class),
											]),
									]),
							]),
					]),
			]);
	}
}
