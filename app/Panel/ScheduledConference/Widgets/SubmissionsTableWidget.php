<?php

namespace App\Panel\ScheduledConference\Widgets;

use App\Panel\ScheduledConference\Resources\SubmissionResource;
use App\Panel\ScheduledConference\Resources\SubmissionResource\Pages\ManageSubmissions;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SubmissionsTableWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $submissionQuery = ManageSubmissions::generateQueryByCurrentUser('My Queue');

        return SubmissionResource::table($table)
            ->heading(__('general.my_submissions'))
            ->query($submissionQuery);
    }
}
