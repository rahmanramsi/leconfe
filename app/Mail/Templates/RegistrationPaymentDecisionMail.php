<?php

namespace App\Mail\Templates;

use App\Classes\Log;
use App\Models\Registration;
use Illuminate\Support\Str;

class RegistrationPaymentDecisionMail extends TemplateMailable
{
    public string $userName;

    public string $paymentStatus;

    public Log $log;

    public function __construct(Registration $registration, string $state)
    {
        $this->userName = $registration->user->full_name;
        $this->paymentStatus = Str::lower($state);

        $this->log = Log::make(
            name: 'email',
            subject: $registration->user,
            description: __('log.email.sent', ['name' => 'Registration Payment Decision']),
        );
    }

    public static function getDefaultSubject(): string
    {
        return 'Registration Payment Decision';
    }

    public static function getDefaultDescription(): string
    {
        return 'This email is sent to the registrant when the conference manager decide registration payment status';
    }

    public static function getDefaultHtmlTemplate(): string
    {
        return <<<'HTML'
            <p>Dear {{ userName }},</p>
            <p>your registration payment status now are {{ paymentStatus }}.</p>
        HTML;
    }
}
