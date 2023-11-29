<?php

namespace App\Panel\Livewire\Submissions\Components\Files;

use App\Constants\SubmissionFileCategory;
use App\Models\Enums\SubmissionStage;
use App\Panel\Livewire\Workflows\Classes\StageManager;

class AbstractFiles extends SubmissionFilesTable
{
    protected ?string $category = SubmissionFileCategory::ABSTRACT_FILES;

    protected string $tableHeading = "Abstract Files";

    protected $listeners = [
        'refreshAbstractsFiles' => '$refresh'
    ];

    public function getTargetCategory(): string
    {
        return $this->getCategory();
    }

    public function getAcceptedFiles(): array
    {
        return StageManager::callForAbstract()
            ->getSetting(
                'allowed_file_types',
                parent::ACCEPTED_FILE_TYPES
            );
    }


    public function isViewOnly(): bool
    {
        return !auth()->user()->can('uploadAbstract', $this->submission);
    }
}
