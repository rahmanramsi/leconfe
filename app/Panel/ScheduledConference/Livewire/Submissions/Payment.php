<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions;

use Livewire\Component;
use App\Models\Timeline;
use App\Models\Submission;
use App\Models\MailTemplate;
use App\Models\Enums\UserRole;
use App\Forms\Components\TinyEditor;
use Illuminate\Support\Facades\Mail;
use App\Models\Enums\SubmissionStatus;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Mail\Templates\ApprovePaymentMail;
use App\Mail\Templates\DeclinePaymentMail;
use App\Panel\ScheduledConference\Resources\SubmissionResource;

class Payment extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Submission $submission;

    protected $listeners = [
        'refreshSubmission' => '$refresh',
    ];

    public function mount(Submission $submission)
    {
    }

    public function declinePaymentAction()
    {
        return Action::make('declineSubmissionAction')
            ->icon('lineawesome-times-solid')
            ->authorize('declinePayment', $this->submission)
            ->label(__('general.decline_submission'))
            ->color('danger')
            ->outlined()
            ->mountUsing(function (Form $form) {
                $mailTemplate = MailTemplate::where('mailable', DeclinePaymentMail::class)->first();
                $form->fill([
                    'email' => $this->submission->user->email,
                    'subject' => $mailTemplate ? $mailTemplate->subject : '',
                    'message' => $mailTemplate ? $mailTemplate->html_template : '',
                ]);
            })
            ->form([
                Fieldset::make('Notification')
                    ->columns(1)
                    ->schema([
                        TextInput::make('email')
                            ->label(__('general.email'))
                            ->readOnly()
                            ->dehydrated(),
                        TextInput::make('subject')
                            ->label(__('general.subject'))
                            ->required(),
                        TinyEditor::make('message')
                            ->label(__('general.message'))
                            ->minHeight(300)
                            ->profile('email')
                            ->columnSpanFull(),
                        Checkbox::make('do-not-notify-author')
                            ->label(__('general.dont_send_notification_to_author'))
                            ->columnSpanFull(),
                    ]),
            ])
            ->action(function (Action $action, array $data) {
                $this->submission->state()->decline();

                if (! $data['do-not-notify-author']) {
                    try {
                        Mail::to($this->submission->user->email)
                            ->send(
                                (new DeclinePaymentMail($this->submission))
                                    ->subjectUsing($data['subject'])
                                    ->contentUsing($data['message'])
                            );
                    } catch (\Exception $e) {
                        $action->failureNotificationTitle(__('general.email_notification_was_not_delivered'));
                        $action->failure();
                    }
                }

                $action->successRedirectUrl(
                    SubmissionResource::getUrl('view', [
                        'record' => $this->submission->getKey(),
                    ])
                );

                $action->success();
            });
    }

    public function approvePaymentAction()
    {
        return Action::make('acceptSubmissionAction')
            // ->authorize('ApprovePayment', $this->submission)
            // ->icon('lineawesome-check-circle-solid')
            // ->color('primary')
            // ->label(__('general.accept_submission'))
            // ->modalSubmitActionLabel(__('general.accept'))
            ->requiresConfirmation();
            // ->mountUsing(function (Form $form) {
            //     $mailTemplate = MailTemplate::where('mailable', ApprovePaymentMail::class)->first();
            //     $form->fill([
            //         'email' => $this->submission->user->email,
            //         'subject' => $mailTemplate ? $mailTemplate->subject : '',
            //         'message' => $mailTemplate ? $mailTemplate->html_template : '',
            //     ]);
            // })
            // ->form([
            //     Fieldset::make('Notification')
            //         ->label(__('general.notification'))
            //         ->columns(1)
            //         ->schema([
            //             TextInput::make('email')
            //                 ->label(__('general.email'))
            //                 ->readOnly()
            //                 ->dehydrated(),
            //             TextInput::make('subject')
            //                 ->label(__('general.subject'))
            //                 ->required(),
            //             TinyEditor::make('message')
            //                 ->label(__('general.message'))
            //                 ->minHeight(300)
            //                 ->profile('email')
            //                 ->columnSpanFull(),
            //             Checkbox::make('do-not-notify-author')
            //                 ->label(__('general.dont_send_notification_to_author'))
            //                 ->columnSpanFull(),
            //         ]),
            // ])
            // ->action(function (Action $action, array $data) {
            //     $this->submission->state()->sendToPresentation();

            //     if (! $data['do-not-notify-author']) {
            //         try {
            //             Mail::to($this->submission->user->email)
            //                 ->send(
            //                     (new ApprovePaymentMail($this->submission))
            //                         ->subjectUsing($data['subject'])
            //                         ->contentUsing($data['message'])
            //                 );
            //         } catch (\Exception $e) {
            //             $action->failureNotificationTitle(__('general.email_notification_was_not_delivered'));
            //             $action->failure();
            //         }
            //     }

            //     $action->successRedirectUrl(
            //         SubmissionResource::getUrl('view', [
            //             'record' => $this->submission->getKey(),
            //         ])
            //     );

            //     $action->success();
            // });
    }

    public function render()
    {
        $submissionParticipant = $this->submission
            ->participants()
            ->whereHas('role', fn (Builder $query) => $query->where('name', UserRole::Author->value))
            ->where('user_id', auth()->user()->id)
            ->limit(1)
            ->first();

        return view('panel.scheduledConference.livewire.submissions.payment', [
            'currentScheduledConference' => app()->getCurrentScheduledConference(),
            'submissionRegistration' => $this->submission->registration,
            'submissionRegistrant' => $this->submission->registration->user ?? null,
            'isSubmissionAuthor' => $submissionParticipant !== null,
            'isRegistrationOpen' => Timeline::isRegistrationOpen(),
            'submissionDecision' => in_array($this->submission->status, [SubmissionStatus::OnReview, SubmissionStatus::Editing, SubmissionStatus::Declined, SubmissionStatus::OnPresentation])
        ]);
    }
}
