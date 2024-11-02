<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions;

use App\Forms\Components\TinyEditor;
use App\Mail\Templates\ApprovePaymentMail;
use App\Mail\Templates\DeclinePaymentMail;
use App\Models\DefaultMailTemplate;
use App\Models\Enums\RegistrationPaymentState;
use App\Models\Enums\RegistrationPaymentType;
use App\Models\Enums\SubmissionStatus;
use App\Models\Enums\UserRole;
use App\Models\Submission;
use App\Models\Timeline;
use App\Notifications\RegistrationPaymentDecision;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class Payment extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Submission $submission;

    protected $listeners = [
        'refreshSubmission' => '$refresh',
    ];

    public function mount(Submission $submission) {}

    public function registrationPolicyAction()
    {
        return Action::make('registrationPolicyAction')
            ->label(__('general.policy'))
            ->modalHeading(__('general.registration_policy'))
            ->icon('heroicon-o-book-open')
            ->size('xs')
            ->link()
            ->infolist([
                TextEntry::make('registration_policy')
                    ->getStateUsing(fn () => app()->getCurrentScheduledConference()->getMeta('registration_policy'))
                    ->formatStateUsing(fn (string $state) => new HtmlString(<<<HTML
                        <div class='user-content'>
                            {$state}
                        </div>
                    HTML))
                    ->label('')
                    ->html(),
            ])
            ->modalSubmitAction(false);
    }

    public function decideRegsitrationAction()
    {
        return Action::make('decideRegsitrationAction')
            ->label(__('general.decision'))
            ->authorize('decideRegistration', $this->submission)
            ->icon('heroicon-o-pencil-square')
            ->color('primary')
            ->size('xs')
            ->modalHeading(__('general.paid_status_decision'))
            ->modalWidth('2xl')
            ->link()
            ->mountUsing(function (Form $form) {
                $registrationPayment = $this->submission->registration->registrationPayment;
                $form->fill([
                    'registrationPayment' => [
                        'state' => $registrationPayment->state,
                        'paid_at' => $registrationPayment->paid_at,
                    ],
                ]);
            })
            ->form([
                Grid::make(1)
                    ->schema([
                        Select::make('registrationPayment.state')
                            ->label(__('general.state'))
                            ->options(RegistrationPaymentState::array())
                            ->native(false)
                            ->required()
                            ->live(),
                        DatePicker::make('registrationPayment.paid_at')
                            ->label(__('general.paid_date'))
                            ->placeholder('Select registration paid date..')
                            ->prefixIcon('heroicon-m-calendar')
                            ->formatStateUsing(fn () => now())
                            ->visible(fn (Get $get): bool => $get('registrationPayment.state') === RegistrationPaymentState::Paid->value)
                            ->required(),
                    ]),
            ])
            ->action(function (Action $action, array $data) {
                $registration = $this->submission->registration;
                $formData = $data['registrationPayment'];

                if ($formData['state'] !== RegistrationPaymentState::Paid->value) {
                    $formData['type'] = null;
                    $formData['paid_at'] = null;
                } else {
                    // manual payment because conference manager set it up
                    $formData['type'] = RegistrationPaymentType::Manual->value;
                }

                try {
                    $registration->registrationPayment()->update(Arr::only($formData, ['state', 'paid_at', 'type']));

                    $registration->user->notify(
                        new RegistrationPaymentDecision(
                            registration: $registration,
                            state: $formData['state'],
                        )
                    );
                } catch (\Throwable $th) {
                    throw $th;
                }

                $action->successRedirectUrl(
                    SubmissionResource::getUrl('view', [
                        'record' => $this->submission->getKey(),
                    ])
                );

                $action->success();
            });
    }

    public function deleteRegistrationAction()
    {
        return Action::make('deleteRegistrationAction')
            ->label(__('general.delete'))
            ->authorize('deleteRegistration', $this->submission)
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->size('xs')
            ->link()
            ->requiresConfirmation()
            ->action(function (Action $action) {

                try {
                    $this->submission->registration->forceDelete();
                } catch (\Throwable $th) {
                    $action->failure();
                    throw $th;
                }

                $action->successRedirectUrl(
                    SubmissionResource::getUrl('view', [
                        'record' => $this->submission->getKey(),
                    ])
                );

                $action->success();
            });
    }

    public function cancelRegistrationAction()
    {
        return Action::make('cancelRegistrationAction')
            ->label(__('general.cancel'))
            ->authorize('cancelRegistration', $this->submission)
            ->icon('heroicon-o-x-mark')
            ->tooltip(__('general.cancel_registration'))
            ->color('danger')
            ->size('xs')
            ->link()
            ->requiresConfirmation()
            ->modalHeading(__('general.cancel_registration'))
            ->action(function (Action $action) {

                try {
                    $this->submission->registration->forceDelete();
                } catch (\Throwable $th) {
                    $action->failure();
                    throw $th;
                }

                $action->successRedirectUrl(
                    SubmissionResource::getUrl('view', [
                        'record' => $this->submission->getKey(),
                    ])
                );

                $action->success();
            });
    }

    public function declinePaymentAction()
    {
        return Action::make('declinePaymentAction')
            ->label(__('general.decline_submission_payment'))
            ->authorize('declinePayment', $this->submission)
            ->icon('lineawesome-times-solid')
            ->color('danger')
            ->outlined()
            ->requiresConfirmation()
            ->modalDescription('Are you sure you want to decline the payment? Previous progress will return to payment (previous files and discussions will not be lost). ')
            ->modalWidth('2xl')
            ->mountUsing(function (Form $form) {
                $mailTemplate = DefaultMailTemplate::where('mailable', DeclinePaymentMail::class)->first();
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
                $this->submission->state()->declinePayment();

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
        return Action::make('approvePaymentAction')
            ->label(__('general.approve_submission_payment'))
            ->authorize('ApprovePayment', $this->submission)
            ->icon('lineawesome-check-circle-solid')
            ->color('primary')
            ->modalWidth('2xl')
            ->modalSubmitActionLabel(__('general.accept'))
            ->mountUsing(function (Form $form) {
                $mailTemplate = DefaultMailTemplate::where('mailable', ApprovePaymentMail::class)->first();
                $form->fill([
                    'email' => $this->submission->user->email,
                    'subject' => $mailTemplate ? $mailTemplate->subject : '',
                    'message' => $mailTemplate ? $mailTemplate->html_template : '',
                ]);
            })
            ->form([
                Fieldset::make('Notification')
                    ->label(__('general.notification'))
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
                $this->submission->state()->approvePayment();

                if (! $data['do-not-notify-author']) {
                    try {
                        Mail::to($this->submission->user->email)
                            ->send(
                                (new ApprovePaymentMail($this->submission))
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
            })
            ->disabled(fn () => ! $this->submission->registration);
    }

    public function render()
    {
        $user = auth()->user();

        return view('panel.scheduledConference.livewire.submissions.payment', [
            'currentScheduledConference' => app()->getCurrentScheduledConference(),
            'submissionRegistration' => $this->submission->registration,
            'submissionRegistrant' => $this->submission->registration->user ?? null,
            'isSubmissionAuthor' => $this->submission->isParticipantAuthor($user),
            'isRegistrationOpen' => Timeline::isRegistrationOpen(),
            'submissionDecision' => ($user->hasAnyRole([UserRole::ConferenceManager, UserRole::Admin]) || $this->submission->isParticipantEditor($user)) &&
                in_array($this->submission->status, [
                    SubmissionStatus::OnReview,
                    SubmissionStatus::PaymentDeclined,
                    SubmissionStatus::OnPresentation,
                    SubmissionStatus::Editing,
                ]),
        ]);
    }
}
