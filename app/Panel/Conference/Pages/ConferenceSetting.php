<?php

namespace App\Panel\Conference\Pages;

use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\Conference\Livewire\EmailSetting;
use App\Panel\Administration\Livewire\SidebarSetting;
use App\Panel\Conference\Livewire\AccessSetting;
use App\Panel\Conference\Livewire\DateAndTimeSetting;
use App\Panel\Conference\Livewire\Forms\Conferences\AdditionalInformationSetting;
use App\Panel\Conference\Livewire\Forms\Conferences\ContactSetting;
use App\Panel\Conference\Livewire\Forms\Conferences\InformationSetting;
use App\Panel\Conference\Livewire\Forms\Conferences\PrivacySetting;
use App\Panel\Conference\Livewire\Forms\Conferences\SetupSetting;
use App\Panel\Conference\Livewire\MastHeadSetting;
use App\Panel\Conference\Livewire\NavigationMenuSetting;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class ConferenceSetting extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms, InteractsWithInfolists;

    protected static ?int $navigationSort = -1;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-s-window';

    protected static string $view = 'panel.conference.pages.conference';

    protected ?string $heading = 'Conference Settings';

    protected static ?string $navigationLabel = 'Conference';

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
                LivewireEntry::make('mast-head')
                    ->livewire(MastHeadSetting::class),
            ]);
    }
}
