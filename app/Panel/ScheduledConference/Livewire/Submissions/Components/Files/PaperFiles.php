<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components\Files;

use App\Constants\SubmissionFileCategory;
use Awcodes\Shout\Components\Shout;

class PaperFiles extends SubmissionFilesTable
{
    protected ?string $category = SubmissionFileCategory::PAPER_FILES;

    protected string $tableHeading;

    public function __construct()
    {
        $this->tableHeading = __('general.papers');
    }

    public function isViewOnly(): bool
    {
        return ! auth()->user()->can('uploadPaper', $this->submission);
    }

    public function uploadFormSchema(): array
    {
        return [
            Shout::make('information')
                ->content(__('general.after_uploading_your_paper_system_will_send_notification')),
            ...parent::uploadFormSchema(),
        ];
    }
}
