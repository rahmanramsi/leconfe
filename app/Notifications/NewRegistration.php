<?php

namespace App\Notifications;

use App\Mail\Templates\NewRegistrationMail;
use App\Models\Registration;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use App\Providers\PanelProvider;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRegistration extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Registration $registration)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new NewRegistrationMail($this->registration))
            ->to($notifiable);
    }

    public function toDatabase(object $notifiable)
    {
        $registrationCost = moneyOrFree($this->registration->registrationPayment->cost, $this->registration->registrationPayment->currency, true);

        return FilamentNotification::make()
            ->icon('heroicon-m-user-plus')
            ->iconColor('primary')
            ->title('New Registrant')
            ->body("
                Name: {$this->registration->user->full_name}<br>
                Type: {$this->registration->registrationPayment->name}<br>
                Cost: {$registrationCost}
            ")
            ->actions([
                Action::make('new-registrant')
                    ->label('Go to registrant list')
                    ->url(fn () => RegistrantResource::getUrl('index', panel: PanelProvider::PANEL_SCHEDULED_CONFERENCE)),
            ])
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
