<?php

namespace App\Notifications;

use App\Mail\Templates\RegistrationPaymentDecisionMail;
use App\Mail\Templates\SubmissionPaymentDecisionMail;
use App\Models\Enums\RegistrationPaymentState;
use App\Models\Registration;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RegistrationPaymentDecision extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Registration $registration, public string $state)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $submission = $this->registration->submission;

        if ($submission) {
            return (new SubmissionPaymentDecisionMail($submission, $this->registration, $this->state))
                ->to($notifiable);
        }

        return (new RegistrationPaymentDecisionMail($this->registration, $this->state))
            ->to($notifiable);
    }

    public function toDatabase(object $notifiable)
    {
        $submission = $this->registration->submission;

        $body = "Dear {$this->registration->user->full_name}, <br>";

        if ($submission) {
            $body .= "Your payment to <strong>{$submission->getMeta('title')}</strong> submission status now are ".($this->state === RegistrationPaymentState::Paid->value ?
            '<strong>paid</strong>.' : '<strong>unpaid</strong>, please finish the payment to continue your submission process.');
        } else {
            $body .= "Your registration ({$this->registration->registrationPayment->name}) payment status now are ".($this->state === RegistrationPaymentState::Paid->value ?
            '<strong>paid</strong>.' : '<strong>unpaid</strong>, please finish the payment to finish your registration.');
        }

        return FilamentNotification::make()
            ->title('Participant Registration')
            ->body($body)
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
