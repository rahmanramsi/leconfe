<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components;

use App\Classes\Log;
use App\Forms\Components\TinyEditor;
use App\Mail\Templates\ParticipantAssignedMail;
use App\Models\DefaultMailTemplate;
use App\Models\Enums\SubmissionStatus;
use App\Models\Enums\UserRole;
use App\Models\RegistrationType;
use App\Models\Submission;
use App\Models\SubmissionParticipant;
use App\Models\User;
use App\Notifications\NewRegistration;
use App\Notifications\ParticipantAssigned;
use App\Panel\ScheduledConference\Resources\RegistrantResource\Pages\EnrollUser;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class ParticipantList extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public Submission $submission;

    public bool $enrollment = false;

    public array $selectedParticipant = [];

    public static function renderSelectParticipant(User $participant): string
    {
        return view('forms.select-participant', ['participant' => $participant])->render();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => $this->submission->participants()->with(['role'])->getQuery()
            )
            ->columns([
                Split::make([
                    SpatieMediaLibraryImageColumn::make('user.profile')
                        ->label(__('general.profile'))
                        ->grow(false)
                        ->collection('profile')
                        ->conversion('avatar')
                        ->width(50)
                        ->height(50)
                        ->defaultImageUrl(
                            fn (SubmissionParticipant $record): string => $record->user->getFilamentAvatarUrl()
                        )
                        ->extraCellAttributes([
                            'style' => 'width: 1px',
                        ])
                        ->circular(),
                    TextColumn::make('user.fullName')
                        ->label(__('general.full_name'))
                        ->description(
                            function (Model $record) {
                                return $record->role->name;
                            }
                        ),
                ]),
            ])
            ->heading(__('general.participants'))
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(__('general.assign_participant'))
                    ->authorize(fn () => auth()->user()->can('assignParticipant', $this->submission))
                    ->hidden($this->submission->isDeclined())
                    ->icon('lineawesome-user-plus-solid')
                    ->label(__('general.assign'))
                    ->link()
                    ->color('primary')
                    ->size('xs')
                    ->extraModalFooterActions(function (Action $action) {
                        return [$action->makeModalSubmitAction('assignAnother', ['another' => true])
                            ->label(__('general.assign_and_another'))];
                    })
                    ->modalSubmitActionLabel(__('general.assign'))
                    ->modalWidth('2xl')
                    ->mountUsing(function (Form $form): void {
                        $mailTemplate = DefaultMailTemplate::where('mailable', ParticipantAssignedMail::class)->first();

                        $form->fill([
                            'subject' => $mailTemplate ? $mailTemplate->subject : '',
                            'message' => $mailTemplate ? $mailTemplate->html_template : '',
                        ]);
                    })
                    ->form([
                        Grid::make(3)
                            ->schema([
                                Select::make('role_id')
                                    ->label('Role')
                                    ->options(function () {
                                        return app()->getCurrentConference()->roles()->whereIn('name', [
                                            UserRole::ScheduledConferenceEditor,
                                            UserRole::TrackEditor,
                                            UserRole::Author,
                                        ])
                                            ->pluck('name', 'id');
                                    })
                                    ->placeholder(__('general.select_role'))
                                    ->columnSpan(1),
                                Select::make('user_id')
                                    ->label(__('general.name'))
                                    ->required()
                                    ->allowHtml()
                                    ->reactive()
                                    ->preload()
                                    ->reactive()
                                    ->options(
                                        fn (Get $get): array => User::with('roles')
                                            ->whereHas(
                                                'roles',
                                                fn (Builder $query) => $query->whereId($get('role_id'))
                                            )
                                            ->whereNotIn('id', $this->submission->participants->pluck('user_id'))
                                            ->get()
                                            ->mapWithKeys(
                                                fn (User $user) => [
                                                    $user->getKey() => static::renderSelectParticipant($user),
                                                ]
                                            )
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->getSearchResultsUsing(function (Get $get, string $search) {
                                        return User::with('roles')
                                            ->whereHas(
                                                'roles',
                                                fn (Builder $query) => $query->whereId($get('role_id'))
                                            )
                                            ->whereNotIn('id', $this->submission->participants->pluck('user_id'))
                                            ->where(function ($query) use ($search) {
                                                $query
                                                    ->where('given_name', 'like', "%{$search}%")
                                                    ->orWhere('family_name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%");
                                            })
                                            ->get()
                                            ->mapWithKeys(
                                                fn (User $user) => [
                                                    $user->getKey() => static::renderSelectParticipant($user),
                                                ]
                                            )
                                            ->toArray();
                                    })
                                    ->columnSpan(2),
                                Fieldset::make()
                                    ->label(__('general.notification'))
                                    ->schema([
                                        TextInput::make('subject')
                                            ->label(__('general.subject'))
                                            ->required()
                                            ->columnSpanFull(),
                                        TinyEditor::make('message')
                                            ->label(__('general.message'))
                                            ->minHeight(300)
                                            ->profile('email')
                                            ->columnSpanFull()
                                            ->toolbarSticky(false),
                                    ]),
                                Checkbox::make('no-notification')
                                    ->label(__('general.dont_send_notification'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->successNotificationTitle(__('general.participant_assigned'))
                    ->action(function (Action $action, array $data) {
                        $submissionParticipant = $this->submission->participants()->create([
                            'user_id' => $data['user_id'],
                            'role_id' => $data['role_id'],
                        ]);

                        $this->dispatch('refreshSubmission');

                        Log::make(
                            name: 'submission',
                            subject: $this->submission,
                            description: __('general.participant_assigned', [
                                'name' => $submissionParticipant->user->fullName,
                                'role' => $submissionParticipant->role->name,
                            ])
                        )
                            ->by(auth()->user())
                            ->properties([
                                'user_id' => $data['user_id'],
                                'role_id' => $data['role_id'],
                            ])
                            ->save();

                        if (! $data['no-notification']) {
                            try {
                                Mail::to($submissionParticipant->user->email)
                                    ->send(
                                        (new ParticipantAssignedMail($submissionParticipant))
                                            ->contentUsing($data['message'])
                                            ->subjectUsing($data['subject'])
                                    );
                            } catch (\Exception $e) {
                                $action->failureNotificationTitle(__('general.email_notification_was_not_delivered'));
                                $action->failure();
                            }
                        }

                        $submissionParticipant->user->notify(
                            new ParticipantAssigned($this->submission)
                        );

                        $action->success();
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('notify-participant')
                        ->authorize('SubmissionParticipant:notify')
                        ->color('primary')
                        ->modalHeading(__('general.notify_participant'))
                        ->icon('iconpark-sendemail')
                        ->modalSubmitActionLabel(__('general.notify'))
                        ->modalWidth('xl')
                        ->visible(
                            fn (Model $record): bool => $record->user->email !== auth()->user()->email
                        )
                        ->mountUsing(function (Form $form) {
                            $form->fill([
                                'subject' => __('general.notification_from_leconfe'), // should it use 'leconfe'
                            ]);
                        })
                        ->form([
                            Grid::make(1)
                                ->schema([
                                    TextInput::make('email')
                                        ->disabled()
                                        ->dehydrated()
                                        ->formatStateUsing(
                                            fn (SubmissionParticipant $record) => $record->user->email
                                        )
                                        ->required()
                                        ->label(__('general.target')),
                                    TextInput::make('subject')
                                        ->label(__('general.subject'))
                                        ->required(),
                                    TinyEditor::make('message')
                                        ->minHeight(300)
                                        ->profile('email')
                                        ->label(__('general.message'))
                                        ->required()
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->label(__('general.notify'))
                        ->successNotificationTitle(__('general.notification_sent'))
                        ->action(function (Action $action, array $data) {
                            Mail::send(
                                [],
                                [],
                                function (Message $message) use ($data) {
                                    $message->to($data['email'])
                                        ->subject($data['subject'])
                                        ->html($data['message']);
                                }
                            );
                            $action->success();
                        }),
                    Impersonate::make()
                        ->grouped()
                        ->visible(
                            fn (Model $record): bool => $record->user->email !== auth()->user()->email && auth()->user()->canImpersonate()
                        )
                        ->label(__('general.login_as'))
                        ->icon('iconpark-login')
                        ->color('primary')
                        ->redirectTo(SubmissionResource::getUrl('view', ['record' => $this->submission]))
                        ->action(function (SubmissionParticipant $record, Impersonate $action) {
                            if (! $action->impersonate($record->user)) {
                                $action->failureNotificationTitle(__('general.user_cant_impersonated'));
                                $action->failure();
                            }
                        }),
                    Action::make('enroll_user')
                        ->authorize('Registration:enroll')
                        ->color('primary')
                        ->icon('heroicon-o-user-plus')
                        ->label(__('general.enroll_user'))
                        ->visible(
                            fn (SubmissionParticipant $record): bool => $this->enrollment &&
                                ! $this->submission->registration &&
                                $this->submission->isParticipantAuthor($record->user)
                        )
                        ->successNotificationTitle(__('general.saved'))
                        ->form(fn (SubmissionParticipant $record) => EnrollUser::enrollForm($record->user, RegistrationType::LEVEL_AUTHOR))
                        ->action(function (Action $action, SubmissionParticipant $record, array $data) {

                            try {
                                $registrationType = RegistrationType::find($data['registration_type_id'])->first();

                                $registration = $this->submission->registration()->create([
                                    'user_id' => $record->user->getKey(),
                                    'registration_type_id' => $registrationType->getKey(),
                                ]);

                                $registration->registrationPayment()->create([
                                    'name' => $registrationType->type,
                                    'level' => $registrationType->level,
                                    'description' => $registrationType->getMeta('description'),
                                    'cost' => $registrationType->cost,
                                    'currency' => $registrationType->currency,
                                    'state' => $data['registrationPayment']['state'],
                                    'paid_at' => $data['registrationPayment']['paid_at'] ?? null,
                                ]);

                                User::whereHas('roles', function ($query) {
                                    $query->whereHas('permissions', function ($query) {
                                        $query->where('name', 'Registration:notified');
                                    });
                                })->get()->each(function ($user) use ($registration) {
                                    $user->notify(
                                        new NewRegistration(
                                            registration: $registration,
                                        )
                                    );
                                });
                            } catch (\Throwable $th) {
                                $action->failure();
                                throw $th;
                            }

                            $action->successRedirectUrl(
                                SubmissionResource::getUrl('view', [
                                    'record' => $this->submission->getKey(),
                                ])
                            );

                            return $action->success();
                        }),
                    Action::make('remove-participant')
                        ->authorize('SubmissionParticipant:delete')
                        ->color('danger')
                        ->icon('iconpark-deletethree-o')
                        ->visible(
                            fn (SubmissionParticipant $record): bool => $record->user->email !== $this->submission->user->email &&
                                ! in_array($this->submission->status, [SubmissionStatus::Published, SubmissionStatus::Declined, SubmissionStatus::Withdrawn]) &&
                                $record->user->getKey() !== auth()->user()->getKey()
                        )
                        ->label(__('general.remove'))
                        ->successNotificationTitle(__('general.participant_removed'))
                        ->action(function (Action $action, Model $record) {
                            $record->delete();
                            $action->success();

                            $this->dispatch('refreshSubmission');
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->paginated(false);
    }

    public function render()
    {
        return view('panel.scheduledConference.livewire.submissions.submission-detail.assign-participants');
    }
}
