<?php

namespace App\Panel\Livewire\Submissions;

use App\Actions\Submissions\SubmissionUpdateAction;
use App\Mail\Templates\AcceptAbstractMail;
use App\Mail\Templates\DeclineAbstractMail;
use App\Models\Enums\SubmissionStage;
use App\Models\Enums\SubmissionStatus;
use App\Models\MailTemplate;
use App\Models\Submission;
use App\Panel\Livewire\Workflows\Classes\StageManager;
use App\Panel\Livewire\Workflows\Concerns\InteractWithTenant;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class CallforAbstract extends Component implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions, InteractWithTenant;

    public Submission $submission;

    public function declineAction()
    {
        return Action::make('decline')
            ->outlined()
            ->color("danger")
            ->authorize('Submission:decline')
            ->modalWidth("2xl")
            ->record($this->submission)
            ->modalHeading("Confirmation")
            ->modalSubmitActionLabel("Decline")
            ->extraAttributes(['class' => 'w-full'], true)
            ->mountUsing(function (Form $form): void {
                $mailTempalte = MailTemplate::where('mailable', DeclineAbstractMail::class)->first();
                $form->fill([
                    'subject' => $mailTempalte ? $mailTempalte->subject : '',
                    'message' => $mailTempalte ? $mailTempalte->html_template : '',
                ]);
            })
            ->form([
                Fieldset::make("Notification")
                    ->columns(1)
                    ->schema([
                        TextInput::make('email')
                            ->hidden(fn (Get $get): bool => $get('no-notification'))
                            ->disabled()
                            ->formatStateUsing(fn (Submission $record): string => $record->user->email),
                        TextInput::make('subject'),
                        TinyEditor::make('message')
                            ->hidden(fn (Get $get): bool => $get('no-notification'))
                            ->minHeight(300),
                        Checkbox::make('no-notification')
                            ->label("Don't send notification to author")
                            ->reactive()
                            ->default(false),
                    ])
            ])
            ->successNotificationTitle("Submission declined")
            ->action(function (Action $action, array $data) {
                SubmissionUpdateAction::run([
                    'stage' => SubmissionStage::CallforAbstract,
                    'status' => SubmissionStatus::Declined
                ], $this->submission);
                // Send mail
                $action->success();
            })
            ->icon("lineawesome-times-circle-solid");
    }

    public function acceptAction()
    {
        return Action::make('accept')
            ->modalHeading("Confirmation")
            ->modalSubmitActionLabel("Accept")
            ->authorize('Submission:accept')
            ->modalWidth("2xl")
            ->record($this->submission)
            ->successNotificationTitle("Accepted")
            ->extraAttributes(['class' => 'w-full'])
            ->icon("lineawesome-check-circle-solid")
            ->mountUsing(function (Form $form): void {
                $mailTemplate = MailTemplate::where('mailable', AcceptAbstractMail::class)->first();
                $form->fill([
                    'message' => $mailTemplate ? $mailTemplate->html_template : ''
                ]);
            })
            ->form([
                Fieldset::make("Notification")
                    ->columns(1)
                    ->schema([
                        /**
                         * TODO:
                         * - We need to create a function for it because it is used frequently.
                         * 
                         * Something like:
                         *   UserNotificaiton::formSchema()
                         */
                        TextInput::make('email')
                            ->hidden(fn (Get $get): bool => $get('no-notification'))
                            ->disabled()
                            ->formatStateUsing(fn (Submission $record): string => $record->user->email),
                        TinyEditor::make('message')
                            ->minHeight(300)
                            ->hidden(fn (Get $get): bool => $get('no-notification')),
                        Checkbox::make('no-notification')
                            ->label("Don't send notification to author")
                            ->reactive()
                            ->default(false),
                    ])
            ])
            ->action(function (Action $action) {
                SubmissionUpdateAction::run([
                    'stage' => SubmissionStage::PeerReview,
                    'status' => SubmissionStatus::OnReview
                ], $this->submission);

                Mail::to($this->submission->user)
                    ->send(new AcceptAbstractMail($this->submission));

                $this->dispatch("refreshPeerReview");
                $action->success();
            });
    }


    public function render()
    {
        return view('panel.livewire.submissions.call-for-abstract', [
            'reviewStageOpen' => StageManager::stage('peer-review')->isStageOpen(),
        ]);
    }
}
