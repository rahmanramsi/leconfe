<?php

namespace App\Mail\Templates;

use App\Models\Registration;

class NewRegistrationMail extends TemplateMailable
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
        return 'New Conference Registrant';
    }

    public static function getDefaultDescription(): string
    {
        return 'This email is sent to conference manager when an user register to conference';
    }

    public static function getDefaultHtmlTemplate(): string
    {
        return <<<'HTML'
            <p>There's a new user that register to {{ conferenceName }}, here's the details:</p>
            <table>
                <tr>
                    <td style="width:100px;">Name</td>
                    <td>:</td>
                    <td>{{ registrantName }}</td>
                </tr>
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
