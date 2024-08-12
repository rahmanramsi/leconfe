<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\Registration\RegistrationPolicies;
use App\Panel\ScheduledConference\Livewire\Registration\RegistrationTypes;
use Illuminate\Contracts\Support\Htmlable;

class RegistrationSettings extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.registration-setting';

    public static function getNavigationGroup(): string
    {
        return __('general.settings');
    }

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-end-on-rectangle';

    public static function getNavigationLabel(): string
    {
        return __('general.registrations');
    }


    public function getHeading(): string|Htmlable
    {
        return __('general.registrations_settings');
    }


    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if ($user->can('RegistrationSetting:viewAny')) {
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
                            ->label(__('general.type'))
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                LivewireEntry::make('registrationType')
                                    ->livewire(RegistrationTypes::class)
                            ]),
                        InfolistsVerticalTabs\Tab::make('Settings')
                            ->label(__('general.settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                LivewireEntry::make('registrationPolicy')
                                    ->livewire(RegistrationPolicies::class)
                            ]),
                    ]),
            ]);
    }
}
