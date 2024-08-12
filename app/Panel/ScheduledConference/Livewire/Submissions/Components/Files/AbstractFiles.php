<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components\Files;

use App\Constants\SubmissionFileCategory;

class AbstractFiles extends SubmissionFilesTable
{
    protected ?string $category = SubmissionFileCategory::ABSTRACT_FILES;

    protected string $tableHeading;

    public function __construct()
    {
        $this->tableHeading = __('general.abstract_files');
    }

    protected $listeners = [
        'refreshAbstractsFiles' => '$refresh',
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

        return ! auth()->user()->can('uploadAbstract', $this->submission);
    }
}
