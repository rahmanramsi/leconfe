<?php

namespace App\Panel\ScheduledConference\Resources\SubmissionResource\Pages;

use App\Models\Enums\SubmissionStatus;
use App\Models\Enums\UserRole;
use App\Models\Submission;
use App\Models\Timeline;
use App\Panel\ScheduledConference\Pages\Workflow;
use App\Panel\ScheduledConference\Pages\WorkflowSetting;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use Awcodes\Shout\Components\ShoutEntry;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ManageSubmissions extends ManageRecords
{
    protected static string $resource = SubmissionResource::class;

    protected static string $view = 'panel.conference.resources.submission-resource.pages.list-submission';

    protected const TAB_MYQUEUE = 'My Queue';

    protected const TAB_ACTIVE = 'Active';

    protected const TAB_UNASSIGNED = 'Unassigned';

    protected const TAB_PRESENTATION = 'Presentation';

    protected const TAB_ARCHIVED = 'Archived';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Settings')
                ->button()
                ->authorize('update', app()->getCurrentScheduledConference())
                ->outlined()
                ->icon('heroicon-o-cog')
                ->url(WorkflowSetting::getUrl()),
            Action::make('create')
                ->button()
                ->disabled(
                    fn (): bool => !Timeline::isSubmissionOpen()
                )
                ->url(static::$resource::getUrl('create'))
                ->icon('heroicon-o-plus')
                ->label(function (Action $action) {
                    if ($action->isDisabled()) {
                        return 'Submission is not open';
                    }

                    return 'Submission';
                }),
        ];
    }

    public static function generateQueryByCurrentUser(string $tab)
    {
        $user = Auth::user();
        $query = static::getResource()::getEloquentQuery()
            ->withCount('editors');

        if ($user->hasAnyRole([
            UserRole::ConferenceEditor,
            UserRole::ConferenceManager,
            UserRole::Admin,
        ])) {
            return $query
            ->when($tab == static::TAB_MYQUEUE, fn ($query) =>  $query->whereIn('status', [
                    SubmissionStatus::Queued,
                    SubmissionStatus::Incomplete,
                ]))
                ->when($tab == static::TAB_PRESENTATION, fn ($query) =>  $query->whereIn('status', [
                    SubmissionStatus::OnPresentation,
                    SubmissionStatus::Editing,
                ]))
                ->when($tab === static::TAB_ARCHIVED, fn ($query) => $query->whereIn('status', [
                    SubmissionStatus::Published,
                    SubmissionStatus::Declined,
                    SubmissionStatus::Withdrawn,
                ]))
                ->when($tab === static::TAB_UNASSIGNED, fn ($query) => $query->having('editors_count', 0))
                ->when($tab === static::TAB_ACTIVE, fn ($query) => $query->having('editors_count', '>', 0)
                    ->whereIn('status', [
                        SubmissionStatus::OnReview,
                    ]));
        }


        if ($user->hasRole(UserRole::Reviewer)) {
            $query->orWhere(fn ($query) => $query->whereHas('reviews', fn (Builder $query) => $query->where('user_id', Auth::id())))
                ->when($tab == static::TAB_MYQUEUE, fn ($query) =>  $query->whereIn('status', [
                    SubmissionStatus::OnReview,
                    SubmissionStatus::Editing,
                    SubmissionStatus::OnPresentation,
                ]))
                ->when($tab == static::TAB_ARCHIVED, fn ($query) => $query->whereIn('status', [
                    SubmissionStatus::Published,
                    SubmissionStatus::Declined,
                    SubmissionStatus::Withdrawn,
                ]));
        }


        if ($user->hasRole(UserRole::Author)) {
            $query->orWhere(fn ($query) => $query->where('user_id', $user->id)
                ->when($tab == static::TAB_PRESENTATION, fn ($query) =>  $query->whereIn('status', [
                    SubmissionStatus::OnPresentation,
                    SubmissionStatus::Editing,
                ]))
                ->when($tab == static::TAB_MYQUEUE, fn ($query) =>  $query->whereIn('status', [
                    SubmissionStatus::Queued,
                    SubmissionStatus::Incomplete,
                    SubmissionStatus::OnReview,
                    SubmissionStatus::Editing,
                ]))
                ->when($tab == static::TAB_ARCHIVED, fn ($query) => $query->whereIn('status', [
                    SubmissionStatus::Published,
                    SubmissionStatus::Declined,
                    SubmissionStatus::Withdrawn,
                ])));
        }

        return $query;
    }

    public function getTabs(): array
    {
        $user = auth()->user();
        $tabs = [
            static::TAB_MYQUEUE => $this->createTab('My Queue', static::TAB_MYQUEUE),
        ];

        if ($user->hasAnyRole([
            UserRole::Admin,
            UserRole::ConferenceManager,
            UserRole::ConferenceEditor,
        ])) {
            $tabs[static::TAB_UNASSIGNED] = $this->createTab('Unassigned', static::TAB_UNASSIGNED);
            $tabs[static::TAB_ACTIVE] = $this->createTab('Active', static::TAB_ACTIVE);
        }

        if ($user->hasAnyRole([
            UserRole::Author,
            UserRole::Admin,
            UserRole::ConferenceManager,
            UserRole::ConferenceEditor,
        ])) {
            $tabs[static::TAB_PRESENTATION] = $this->createTab('Presentation', static::TAB_PRESENTATION);
        }

        $tabs[static::TAB_ARCHIVED] = $this->createTab('Archived', static::TAB_ARCHIVED);

        return $tabs;
    }

    private function createTab(string $label, string $tabType): Tab
    {
        $query = static::generateQueryByCurrentUser($tabType);

        $count = $query->count();

        return Tab::make($label)
            ->modifyQueryUsing(fn (): Builder => $query)
            ->badge(fn (): int => $count)
            ->badgeColor(fn (): string => $count > 0 ? 'primary' : 'gray');
    }
}
