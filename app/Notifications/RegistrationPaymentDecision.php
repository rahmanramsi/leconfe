<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use App\Providers\PanelProvider;
use Filament\Notifications\Actions\Action;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Enums\RegistrationPaymentState;
use Illuminate\Notifications\Messages\MailMessage;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use Filament\Notifications\Notification as FilamentNotification;

class RegistrationPaymentDecision extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user, public string $state = RegistrationPaymentState::Unpaid->value)
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
        return ['database'];
    }

    public function toDatabase(object $notifiable)
    {
        $state = $this->state === RegistrationPaymentState::Paid->value ? 'accepted' : 'declined';

        return FilamentNotification::make()
            ->title('Participant Registration')
            ->body("Dear {$this->user->full_name}, your payment has been {$state}, please finish your registration payment.")
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
