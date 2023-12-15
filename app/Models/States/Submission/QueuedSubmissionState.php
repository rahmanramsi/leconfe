<?php

namespace App\Models\States\Submission;

use App\Actions\Submissions\SubmissionUpdateAction;
use App\Models\Enums\SubmissionStage;
use App\Models\Enums\SubmissionStatus;

class QueuedSubmissionState extends BaseSubmissionState
{
    public function acceptAbstract(): void
    {
        SubmissionUpdateAction::run([
            'stage' => SubmissionStage::Payment,
            'status' => SubmissionStatus::Payment,
        ], $this->submission);
    }

    public function decline():void
    {
        SubmissionUpdateAction::run([
            'status' => SubmissionStatus::Declined,
        ], $this->submission);
    }

    public function withdraw(): void
    {
        SubmissionUpdateAction::run(['status' => SubmissionStatus::Withdrawn], $this->submission);
    }
}
