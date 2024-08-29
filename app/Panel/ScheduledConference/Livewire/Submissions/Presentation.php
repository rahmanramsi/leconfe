<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions;

use App\Mail\Templates\AcceptAbstractMail;
use App\Mail\Templates\DeclineAbstractMail;
use App\Models\Enums\SubmissionStatus;
use App\Models\Enums\UserRole;
use App\Models\MailTemplate;
use App\Models\Role;
use App\Models\Submission;
use App\Notifications\AbstractAccepted;
use App\Notifications\AbstractDeclined;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Forms\Components\TinyEditor;

class Presentation extends Component implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    public Submission $submission;

    protected $listeners = [
        'refreshSubmission' => '$refresh',
    ];

    public function sendToEditingAction()
    {
        return Action::make('sendToEditing')
            ->label(__('general.send_to_editing'))
            ->modalHeading(__('general.send_to_editing'))
            ->modalSubmitActionLabel(__('general.send_to_editing'))
            ->authorize('acceptAbstract', $this->submission)
            ->modalWidth('2xl')
            ->record($this->submission)
            ->successNotificationTitle(__('general.accepted'))
            ->extraAttributes(['class' => 'w-full'])
            ->requiresConfirmation()
            ->icon('lineawesome-check-circle-solid')
            ->action(
                function (Action $action, array $data) {
                    try {
                        $this->submission->state()->sendToEditing();

                        $action->successRedirectUrl(
                            SubmissionResource::getUrl('view', [
                                'record' => $this->submission->getKey(),
                            ])
                        );

                        $action->success();
                    } catch (\Throwable $th) {
                        Log::error($th->getMessage());
                        $action->failureNotificationTitle(__('general.failed_to_send_to_editing'));
                        $action->failure();
                    }
                }
            );
    }

    public function render()
    {
        $user = auth()->user();

        return view('panel.scheduledConference.livewire.submissions.presentation', [
            'submissionDecision' => ($user->hasAnyRole([UserRole::ConferenceManager, UserRole::Admin]) || $this->submission->isParticipantEditor($user)) && 
            in_array($this->submission->status, [
                SubmissionStatus::OnReview, 
                SubmissionStatus::Editing, 
                SubmissionStatus::Declined
            ]),
        ]);
    }
}
