<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components\Files;

use App\Constants\SubmissionFileCategory;

class ReviewerFiles extends SubmissionFilesTable
{
    protected ?string $category = SubmissionFileCategory::REVIEWER_FILES;

    protected string $tableHeading;

    public function __construct()
    {
        $this->tableHeading = __('general.reviewer_files');
    }

}
