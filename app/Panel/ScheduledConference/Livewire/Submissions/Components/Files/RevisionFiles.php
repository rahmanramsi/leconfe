<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components\Files;

use App\Constants\SubmissionFileCategory;
use App\Models\Enums\SubmissionStage;
use Awcodes\Shout\Components\Shout;

class RevisionFiles extends SubmissionFilesTable
{
    protected ?string $category = SubmissionFileCategory::REVISION_FILES;

    protected string $tableHeading;

    protected string $tableDescription;

    public function __construct()
    {
        $this->tableHeading = __('general.revisions');
        $this->tableDescription = __('general.upload_your_reviews_files_here');
    }

    public function isViewOnly(): bool
    {
        if ($this->submission->stage !== SubmissionStage::PeerReview) {
            return true;
        }

        return ! auth()->user()->can('uploadRevisionFiles', $this->submission) && ! $this->submission->revision_required;
    }

    public function uploadFormSchema(): array
    {
        return [
            Shout::make('information')
                ->content(__('general.after_uploading_files_system_will_send_notification_to_editor')),
            ...parent::uploadFormSchema(),
        ];
    }
}
