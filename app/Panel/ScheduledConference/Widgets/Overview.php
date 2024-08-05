<?php

namespace App\Panel\ScheduledConference\Widgets;

use App\Models\AuthorRole;
use App\Models\Enums\SubmissionStatus;
use App\Models\Enums\UserRole;
use App\Models\Submission;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Overview extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Submitted Papers', Submission::count())
                ->icon('heroicon-o-document-text')
                ->description('Total number of papers submitted'),
            Stat::make('Accepted Papers', Submission::query()
                ->whereIn('status', [
                    SubmissionStatus::OnReview,
                    SubmissionStatus::OnPresentation,
                    SubmissionStatus::Editing,
                    SubmissionStatus::Published,
                ])
                ->where('published_at', '>=', now()->subMonth())
                ->count())
                ->description('Accepted paper in the last 30 days'),
            Stat::make('New Authors', User::query()
                ->where('created_at', '>=', now()->subMonth())
                ->whereHas('roles', fn($query) => $query->where('name', UserRole::Author->value))
                ->count())
                ->icon('heroicon-o-user-group')
                ->description('New authors in the last 30 days'),
        ];
    }
}
