<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Components\Tabs;
use App\Infolists\Components\LivewireEntry;
use App\Panel\Administration\Livewire\SidebarSetting;
use App\Panel\Conference\Livewire\NavigationMenuSetting;
use App\Panel\ScheduledConference\Livewire\ContactSetting;
use App\Panel\ScheduledConference\Livewire\MastHeadSetting;
use App\Panel\ScheduledConference\Livewire\SetupSetting;
use App\Panel\ScheduledConference\Livewire\SponsorSetting;
use App\Panel\ScheduledConference\Livewire\TopicTable;

class ScheduledConferenceSetting extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.scheduled-conference-setting';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Scheduled Conference';

    public function mount(): void
    {
        $this->authorize('update', App::getCurrentScheduledConference());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('update', App::getCurrentScheduledConference());
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make()
                    ->contained(false)
                    ->tabs([
                        Tabs\Tab::make('Masthead')
                            ->schema([
                                LivewireEntry::make('masthead')
                                    ->livewire(MastHeadSetting::class),
                            ]),
                        Tabs\Tab::make('Contact')
                            ->schema([
                                LivewireEntry::make('contact')
                                    ->livewire(ContactSetting::class),
                            ]),
                    ]),
            ]);
    }
}
