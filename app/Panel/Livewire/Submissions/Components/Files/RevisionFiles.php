<?php

namespace App\Panel\Livewire\Submissions\Components\Files;

use App\Constants\SubmissionFileCategory;
use App\Models\Enums\SubmissionStage;
use Awcodes\Shout\Components\Shout;

class RevisionFiles extends SubmissionFilesTable
{
    protected ?string $category = SubmissionFileCategory::REVISION_FILES;

    protected string $tableHeading = "Revisions";

    public function isViewOnly(): bool
    {
        return $this->submission->stage != SubmissionStage::PeerReview || !$this->submission->revision_required;
    }

    public function uploadFormSchema(): array
    {
        return [
            Shout::make('information')
                ->content("After uploading files, system will send notification to the editor."),
            ...parent::uploadFormSchema()
        ];
    }
}
