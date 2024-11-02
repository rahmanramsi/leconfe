<?php

namespace App\Panel\ScheduledConference\Pages;

use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs as InfolistsVerticalTabs;
use App\Panel\Conference\Livewire\EmailSetting;
use App\Panel\ScheduledConference\Livewire\AuthorGuidance;
use App\Panel\ScheduledConference\Livewire\AuthorRoleTable;
use App\Panel\ScheduledConference\Livewire\ReviewGuidance;
use App\Panel\ScheduledConference\Livewire\ReviewSetupSetting;
use App\Panel\ScheduledConference\Livewire\SubmissionFileTypeTable;
use App\Panel\ScheduledConference\Livewire\TopicTable;
use App\Panel\ScheduledConference\Livewire\TrackTable;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class WorkflowSetting extends Page
{
    protected static string $view = 'panel.scheduledConference.pages.workflow-setting';

    public static function getNavigationGroup(): string
    {
        return __('general.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.workflow');
    }

    public function getHeading(): string|Htmlable
    {
        return __('general.workflow_settings');
    }

    protected static ?string $navigationIcon = 'heroicon-o-window';

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
                            ->label(__('general.submissions'))
                            ->schema([
                                InfolistsVerticalTabs\Tabs::make()
                                    ->schema([
                                        InfolistsVerticalTabs\Tab::make('Components')
                                            ->label(__('general.components'))
                                            ->schema([
                                                LivewireEntry::make('submission-file-type-table')
                                                    ->livewire(SubmissionFileTypeTable::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Author Guidance')
                                            ->label(__('general.author_guidance'))
                                            ->schema([
                                                LivewireEntry::make('author-guidance')
                                                    ->livewire(AuthorGuidance::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Author Roles')
                                            ->label(__('general.author_roles'))
                                            ->schema([
                                                LivewireEntry::make('author-roles')
                                                    ->livewire(AuthorRoleTable::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Tracks')
                                            ->label(__('general.track'))
                                            ->schema([
                                                LivewireEntry::make('tracks')
                                                    ->livewire(TrackTable::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Topics')
                                            ->label(__('general.topic'))
                                            ->schema([
                                                LivewireEntry::make('topics')
                                                    ->livewire(TopicTable::class),
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Review')
                            ->label(__('general.review'))
                            ->schema([
                                InfolistsVerticalTabs\Tabs::make()
                                    ->schema([
                                        InfolistsVerticalTabs\Tab::make('Setup')
                                            ->label(__('general.setup'))
                                            ->schema([
                                                LivewireEntry::make('review-setup')
                                                    ->livewire(ReviewSetupSetting::class),
                                            ]),
                                        InfolistsVerticalTabs\Tab::make('Reviewer Guidance')
                                            ->label(__('general.reviewer_guidance'))
                                            ->schema([
                                                LivewireEntry::make('review-guidance')
                                                    ->livewire(ReviewGuidance::class),
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Emails')
                            ->label(__('general.email'))
                            ->schema([
                                LivewireEntry::make('email-setting')
                                    ->livewire(EmailSetting::class),
                            ]),
                    ]),
            ]);
    }
}
