<?php

namespace App\Panel\ScheduledConference\Livewire\Wizards\SubmissionWizard\Steps;

use App\Mail\Templates\ThankAuthorMail;
use App\Models\Enums\UserRole;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\NewSubmission;
use App\Panel\ScheduledConference\Livewire\Wizards\SubmissionWizard\Contracts\HasWizardStep;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ReviewStep extends Component implements HasActions, HasForms, HasWizardStep
{
    use InteractsWithActions, InteractsWithForms;

    public Submission $record;

    protected $listeners = ['refreshLivewire' => '$refresh'];

    public static function getWizardLabel(): string
    {
        return __('general.review');
    }

    public function submitAction()
    {
        return Action::make('submitAction')
            ->label(__('general.submit'))
            ->modalWidth('xl')
            ->modalAlignment('center')
            ->requiresConfirmation()
            ->modalHeading(__('general.submit_abstract'))
            ->modalDescription(function (): string {
                return __('general.review_submission');
            })
            ->modalSubmitActionLabel(__('general.submit'))
            ->successNotificationTitle(__('general.abstract_submitted_wait'))
            ->successRedirectUrl(fn (): string => SubmissionResource::getUrl('complete', ['record' => $this->record]))
            ->action(function (Action $action) {
                try {
                    $this->record->state()->fulfill();

                    Mail::to($this->record->user)->send(
                        new ThankAuthorMail($this->record)
                    );

                    User::role([UserRole::Admin->value, UserRole::ConferenceManager->value])
                        ->lazy()
                        ->each(fn ($user) => $user->notify(new NewSubmission($this->record)));
                } catch (\Exception $e) {
                    $action->failureNotificationTitle(__('general.failed_send_notification'));
                    $action->failure();
                }

                $action->success();
                $action->dispatchSuccessRedirect();
            });
    }

    public function render()
    {
        return view('panel.scheduledConference.livewire.wizards.submission-wizard.steps.review-step');
    }
}
