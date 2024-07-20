<?php

namespace App\Panel\Conference\Pages;

use App\Infolists\Components\LivewireEntry;
use App\Panel\Conference\Livewire\MastHeadSetting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
