<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components\Files;

use App\Constants\SubmissionFileCategory;

class PresentationFiles extends SubmissionFilesTable
{
    protected ?string $category = SubmissionFileCategory::PRESENTATION_FILES;

    protected string $tableHeading;

    public function __construct()
    {
        $this->tableHeading = __('general.presentation_files');
    }

    protected $listeners = [
        'refreshPresentationFiles' => '$refresh',
    ];

    public function getTargetCategory(): string
    {
        return $this->getCategory();
    }

    public function isViewOnly(): bool
    {
        if ($this->viewOnly) {
            return true;
        }

        return ! auth()->user()->can('uploadPresentation', $this->submission);
    }
}
