<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\Registration\RegistrationSetting;
use App\Panel\ScheduledConference\Livewire\Registration\RegistrationTypeTable;

class Registrations extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.registration-setting';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-end-on-rectangle';

    protected static ?string $navigationLabel = 'Registrations';

    protected static ?string $title = 'Registration Settings';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->can('RegistrationSetting:viewAny');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistsVerticalTabs\Tabs::make()
                    ->schema([
                        InfolistsVerticalTabs\Tab::make('Type')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                LivewireEntry::make('registrationType')
                                    ->livewire(RegistrationTypeTable::class)
                            ]),
                        InfolistsVerticalTabs\Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                LivewireEntry::make('registrationSettings')
                                    ->livewire(RegistrationSetting::class)
                            ]),
                    ]),
            ]);
    }
}
