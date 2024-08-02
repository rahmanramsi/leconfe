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

    protected const TAB_PUBLISHED = 'Published';

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

    public static function generateQueryByCurrentUser(string $tabs)
    {
        $statuses = match ($tabs) {
            static::TAB_MYQUEUE => [
                SubmissionStatus::Queued,
            ],
            static::TAB_ACTIVE => [
                SubmissionStatus::OnReview,
                SubmissionStatus::Editing,
                SubmissionStatus::OnPresentation,
            ],
            static::TAB_PUBLISHED => [
                SubmissionStatus::Published,
            ],
            static::TAB_ARCHIVED => [
                SubmissionStatus::Declined,
                SubmissionStatus::Withdrawn,
            ],
            default => null,
        };

        $query = static::getResource()::getEloquentQuery();
        // if (Auth::user()->hasAnyRole([
        //     UserRole::Admin->value,
        //     UserRole::ConferenceManager->value,
        //     UserRole::ConferenceEditor->value,
        // ])) {
        //     return $query->whereIn('status', $statuses)->when(
        //         $tabs == static::TAB_MYQUEUE,
        //         function (Builder $query) {
        //             $query->orWhere([
        //                 ['user_id', '=', Auth::id()],
        //                 ['status', '=', SubmissionStatus::Incomplete],
        //             ]);
        //         }
        //     );
        // }

        // Digunakan untuk menentukan mengetahui kondisi sebelumnya sudah ada atau belum
        $conditionBeforeExist = false;

        return $query->when(
            Auth::user()->hasRole(UserRole::Author->value),
            function (Builder $query) use ($statuses, &$conditionBeforeExist) {
                $query->where('user_id', Auth::id())->whereIn('status', $statuses);
                $conditionBeforeExist = true;
            }
        )->when(
            Auth::user()->hasRole(UserRole::Reviewer->value),
            function (Builder $query) use (&$conditionBeforeExist, $tabs, $statuses) {
                $query->when(
                    $conditionBeforeExist,
                    function (Builder $query) {
                        $query->orWhereHas('reviews', function (Builder $query) {
                            return $query->where('user_id', Auth::id());
                        });
                    },
                    function (Builder $query) {
                        $query->whereHas('reviews', function (Builder $query) {
                            return $query->where('user_id', Auth::id());
                        });
                    }
                )->when($tabs != static::TAB_MYQUEUE, function (Builder $query) use ($statuses) {
                    $query->whereIn('status', $statuses);
                });
                $conditionBeforeExist = true;
            }
        )->when(
            Auth::user()->hasRole(UserRole::ConferenceEditor->value),
            function (Builder $query) use ($statuses, &$conditionBeforeExist) {
                $query->when($conditionBeforeExist, function (Builder $query) {
                    $query->orWhereHas('participants', function (Builder $query) {
                        return $query->where('user_id', Auth::id());
                    });
                }, function (Builder $query) {
                    $query->whereHas('participants', function (Builder $query) {
                        return $query->where('user_id', Auth::id());
                    });
                })->whereIn('status', $statuses);
            }
        )->when(
            $tabs == static::TAB_MYQUEUE,
            function (Builder $query) {
                $query->orWhere([
                    ['user_id', '=', Auth::id()],
                    ['status', '=', SubmissionStatus::Incomplete],
                ]);
            }
        );
    }

    /** Need to be optimized */
    public function getTabs(): array
    {
        return [
            static::TAB_MYQUEUE => $this->createTab('My Queue', static::TAB_MYQUEUE),
            static::TAB_ACTIVE => $this->createTab('Active', static::TAB_ACTIVE),
            static::TAB_PUBLISHED => $this->createTab('Published', static::TAB_PUBLISHED),
            static::TAB_ARCHIVED => $this->createTab('Archived', static::TAB_ARCHIVED),
        ];
    }

    private function createTab(string $label, string $tabType): Tab
    {
        $query = static::generateQueryByCurrentUser($tabType);
        dd($query->toRawSql());

        $count = $query->count();

        return Tab::make($label)
            ->modifyQueryUsing(fn (): Builder => $query)
            ->badge(fn (): int => $count)
            ->badgeColor(fn (): string => $count > 0 ? 'primary' : 'gray');
    }
}
