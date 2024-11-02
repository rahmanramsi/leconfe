<?php

namespace App\Mail\Templates;

use App\Classes\Log;
use App\Models\Registration;
use App\Models\Submission;
use Illuminate\Support\Str;

class SubmissionPaymentDecisionMail extends TemplateMailable
{
    public string $userName;

    public string $submissionTitle;

    public string $paymentStatus;

    public Log $log;

    public function __construct(Submission $submission, Registration $registration, string $state)
    {
        $this->userName = $registration->user->full_name;
        $this->submissionTitle = $submission->getMeta('title');
        $this->paymentStatus = Str::lower($state);

        $this->log = Log::make(
            name: 'email',
            subject: $registration->user,
            description: __('log.email.sent', ['name' => 'Registration Payment Decision']),
        );
    }

    public static function getDefaultSubject(): string
    {
        return 'Submission Payment Decision';
    }

    public static function getDefaultDescription(): string
    {
        return 'This email is sent to the registrant when the conference manager decide submission payment status';
    }

    public static function getDefaultHtmlTemplate(): string
    {
        return <<<'HTML'
            <p>Dear {{ userName }},</p>
            <p>your payment to {{ submissionTitle }} status now are {{ paymentStatus }}.</p>
        HTML;
    }
}
