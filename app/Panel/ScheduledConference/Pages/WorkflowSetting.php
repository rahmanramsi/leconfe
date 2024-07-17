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
use App\Panel\ScheduledConference\Livewire\AuthorGuidance;
use App\Panel\ScheduledConference\Livewire\ContactSetting;
use App\Panel\ScheduledConference\Livewire\InformationSetting;
use App\Panel\ScheduledConference\Livewire\MastHeadSetting;
use App\Panel\ScheduledConference\Livewire\SetupSetting;
use App\Panel\ScheduledConference\Livewire\SponsorSetting;
use App\Panel\ScheduledConference\Livewire\TopicTable;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\ScheduledConference\Livewire\SubmissionFileTypeTable;

class WorkflowSetting extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.workflow-setting';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-window';

    protected static ?string $navigationLabel = 'Workflow Setting';

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
                        Tabs\Tab::make('Submission')
                            ->schema([
                                InfolistsVerticalTabs\Tabs::make()
                                    ->schema([
                                        InfolistsVerticalTabs\Tab::make('Author Guidance')
                                            ->schema([
                                                LivewireEntry::make('author-guidance')
                                                    ->livewire(AuthorGuidance::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Components')
                                            ->schema([
                                                LivewireEntry::make('submission-file-type-table')
                                                    ->livewire(SubmissionFileTypeTable::class),
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Review')
                            ->schema([]),
                        Tabs\Tab::make('Topics')
                            ->schema([]),

                    ]),
            ]);
    }
}
