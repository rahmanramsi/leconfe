<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\Registration\RegistrationPolicyPage;
use App\Panel\ScheduledConference\Livewire\Registration\RegistrationTypePage;

class RegistrationSettings extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.registration-setting';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-end-on-rectangle';

    protected static ?string $navigationLabel = 'Registration';

    protected static ?string $title = 'Registration Settings';

    protected static ?int $navigationSort = 2;

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
                        InfolistsVerticalTabs\Tab::make('Type')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                LivewireEntry::make('registration_type')
                                    ->livewire(RegistrationTypePage::class)
                            ]),
                        InfolistsVerticalTabs\Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                LivewireEntry::make('registration_policy')
                                    ->livewire(RegistrationPolicyPage::class)
                            ]),
                    ]),
            ]);
    }
}
