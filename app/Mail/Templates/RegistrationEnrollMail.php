<?php

namespace App\Mail\Templates;

use App\Models\Registration;

class RegistrationEnrollMail extends TemplateMailable
{
    public string $conferenceName;

    public string $registrantName;

    public string $registrationType;

    public string $registrationCost;

    public string $registrationStatus;

    public function __construct(Registration $registration)
    {
        $this->conferenceName = $registration->scheduledConference->title;
        $this->registrantName = $registration->user->full_name;
        $this->registrationType = $registration->registrationPayment->name;
        $this->registrationCost = moneyOrFree($registration->registrationPayment->cost, $registration->registrationPayment->currency, true);
        $this->registrationStatus = $registration->registrationPayment->state;
    }

    public static function getDefaultSubject(): string
    {
        return '{{ conferenceName }} Registration';
    }

    public static function getDefaultDescription(): string
    {
        return 'This email is sent to user when manager enroll them to conference';
    }

    public static function getDefaultHtmlTemplate(): string
    {
        return <<<'HTML'
            <p>Dear {{ registrantName }}</p>
            <p>You've been enrolled to {{ conferenceName }}, here's the details:</p>
            <table>
                <tr>
                    <td style="width:100px;">Registration Type</td>
                    <td>:</td>
                    <td>{{ registrationType }}</td>
                </tr>
                <tr>
                    <td style="width:100px;">Registration Cost</td>
                    <td>:</td>
                    <td>{{ registrationCost }}</td>
                </tr>
                <tr>
                    <td style="width:100px;">Registration Status</td>
                    <td>:</td>
                    <td>{{ registrationStatus }}</td>
                </tr>
            </table>
        HTML;
    }
}
