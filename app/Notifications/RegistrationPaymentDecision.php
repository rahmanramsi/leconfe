<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use App\Providers\PanelProvider;
use Filament\Notifications\Actions\Action;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Enums\RegistrationPaymentState;
use Illuminate\Notifications\Messages\MailMessage;
use App\Mail\Templates\RegistrationPaymentDecisionMail;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use Filament\Notifications\Notification as FilamentNotification;

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
        return (new RegistrationPaymentDecisionMail($this->registration))
            ->to($notifiable);
    }

    public function toDatabase(object $notifiable)
    {
        $paymentStatus = Str::lower($this->state);
        
        return FilamentNotification::make()
            ->title('Participant Registration')
            ->body("
                Dear {$this->registration->user->full_name}, <br>
                your payment status are {$paymentStatus}, please finish your registration process.
            ")
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
