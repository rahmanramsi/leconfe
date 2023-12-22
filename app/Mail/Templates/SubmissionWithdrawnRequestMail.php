<?php

namespace App\Mail\Templates;

use App\Models\Submission;

class SubmissionWithdrawnRequestMail extends TemplateMailable
{
    public string $title;

    public array $logDetail;

    public function __construct(Submission $submission)
    {
        $this->title = $submission->getMeta('title');

        $this->logDetail = [
            'subject_type' => $submission::class,
            'subject_id' => $submission->getKey(),
            'name' => $this->getDefaultSubject()
        ];
    }

    public static function getDefaultSubject(): string
    {
        return 'Submission Withdraw Request';
    }

    public static function getDefaultDescription(): string
    {
        return 'This email is sent to authors when their submission is withdrawn';
    }

    public static function getDefaultHtmlTemplate(): string
    {
        return <<<'HTML'
            <p>This is an automated notification. We wanted to inform you that your submission titled "{{ title }}" has been requested to be withdrawn.</p>
        HTML;
    }
}
