<?php

namespace App\Models\States\Submission\Concerns;

use App\Actions\Submissions\SubmissionUpdateAction;
use App\Classes\Log;
use App\Models\Enums\SubmissionStage;
use App\Models\Enums\SubmissionStatus;

trait CanDeclinePayment
{
    public function declinePayment(): void
    {
        SubmissionUpdateAction::run([
            'stage' => SubmissionStage::Payment,
            'status' => SubmissionStatus::PaymentDeclined,
        ], $this->submission);

        Log::make(
            name: 'submission',
            subject: $this->submission,
            description: __('general.submission_payment_declined'),
            event : 'submission-payment-declined',
        )
            ->by(auth()->user())
            ->save();
    }
}
