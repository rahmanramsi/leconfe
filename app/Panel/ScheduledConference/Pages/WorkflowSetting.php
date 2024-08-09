<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Components\Tabs;
use App\Infolists\Components\LivewireEntry;
use App\Panel\ScheduledConference\Livewire\AuthorGuidance;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\Conference\Livewire\EmailSetting;
use App\Panel\ScheduledConference\Livewire\AuthorRoleTable;
use App\Panel\ScheduledConference\Livewire\ReviewGuidance;
use App\Panel\ScheduledConference\Livewire\ReviewSetupSetting;
use App\Panel\ScheduledConference\Livewire\SubmissionFileTypeTable;
use App\Panel\ScheduledConference\Livewire\TimelineTable;
use App\Panel\ScheduledConference\Livewire\TopicTable;
use App\Panel\ScheduledConference\Livewire\TrackTable;

class WorkflowSetting extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.workflow-setting';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-window';

    protected static ?string $navigationLabel = 'Workflow';

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
                                        InfolistsVerticalTabs\Tab::make('Components')
                                            ->schema([
                                                LivewireEntry::make('submission-file-type-table')
                                                    ->livewire(SubmissionFileTypeTable::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Author Guidance')
                                            ->schema([
                                                LivewireEntry::make('author-guidance')
                                                    ->livewire(AuthorGuidance::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Author Roles')
                                            ->schema([
                                                LivewireEntry::make('author-roles')
                                                    ->livewire(AuthorRoleTable::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Tracks')
                                            ->schema([
                                                LivewireEntry::make('tracks')
                                                    ->livewire(TrackTable::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Topics')
                                            ->schema([
                                                LivewireEntry::make('topics')
                                                    ->livewire(TopicTable::class),
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Review')
                            ->schema([
                                InfolistsVerticalTabs\Tabs::make()
                                    ->schema([
                                        InfolistsVerticalTabs\Tab::make('Setup')
                                            ->schema([
                                                LivewireEntry::make('review-setup')
                                                    ->livewire(ReviewSetupSetting::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Reviewer Guidance')
                                            ->schema([
                                                LivewireEntry::make('review-guidance')
                                                    ->livewire(ReviewGuidance::class),
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Timeline')
                            ->schema([
                                LivewireEntry::make('timeline-table')
                                    ->livewire(TimelineTable::class),
                            ]),
                        Tabs\Tab::make('Emails')
                            ->schema([
                                LivewireEntry::make('email-setting')
                                    ->livewire(EmailSetting::class),
                            ]),
                    ]),
            ]);
    }
}
