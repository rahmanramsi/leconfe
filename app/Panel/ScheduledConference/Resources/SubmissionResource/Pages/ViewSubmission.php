<?php

namespace App\Panel\ScheduledConference\Resources\SubmissionResource\Pages;

use App\Models\User;
use Filament\Forms\Form;
use App\Models\MailTemplate;
use Filament\Actions\Action;
use App\Models\Enums\UserRole;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Mail;
use App\Models\Enums\SubmissionStage;
use App\Models\Enums\SubmissionStatus;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Awcodes\Shout\Components\ShoutEntry;
use Filament\Forms\Components\TextInput;
use App\Notifications\SubmissionWithdrawn;
use Illuminate\Contracts\Support\Htmlable;
use App\Infolists\Components\LivewireEntry;
use Illuminate\View\Compilers\BladeCompiler;
use App\Mail\Templates\PublishSubmissionMail;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Notifications\SubmissionWithdrawRequested;
use App\Actions\Submissions\AcceptWithdrawalAction;
use App\Actions\Submissions\CancelWithdrawalAction;
use App\Actions\Submissions\RequestWithdrawalAction;
use App\Infolists\Components\VerticalTabs\Tab as Tab;
use App\Panel\ScheduledConference\Livewire\Submissions\Editing;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use App\Infolists\Components\VerticalTabs\Tabs as Tabs;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use App\Panel\ScheduledConference\Livewire\Submissions\PeerReview;
use Filament\Infolists\Components\Tabs as HorizontalTabs;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use App\Panel\ScheduledConference\Livewire\Submissions\Forms\Detail;
use Filament\Infolists\Components\Tabs\Tab as HorizontalTab;
use App\Panel\ScheduledConference\Livewire\Submissions\CallforAbstract;
use App\Panel\ScheduledConference\Livewire\Submissions\Forms\References;
use App\Forms\Components\TinyEditor;
use App\Panel\ScheduledConference\Livewire\Submissions\Components\GalleyList;
use App\Panel\ScheduledConference\Livewire\Submissions\Components\ActivityLogList;
use App\Panel\ScheduledConference\Livewire\Submissions\Components\ContributorList;
use App\Panel\ScheduledConference\Livewire\Submissions\Components\SubmissionProceeding;
use App\Panel\ScheduledConference\Livewire\Submissions\Presentation;
use Filament\Support\Enums\MaxWidth;

class ViewSubmission extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms, InteractsWithInfolists, InteractsWithRecord;

    protected static string $resource = SubmissionResource::class;

    protected static string $view = 'panel.conference.resources.submission-resource.pages.view-submission';

    public function mount($record): void
    {
        static::authorizeResourceAccess();

        $this->record = $this->resolveRecord($record);

        dd($this->record->user->hasCompletedRegistration());

        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumb = $this->getBreadcrumb();

        return [
            $resource::getUrl() => $resource::getBreadcrumb(),
            ...(filled($breadcrumb) ? [$breadcrumb] : []),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->outlined()
                ->url(route('livewirePageGroup.conference.pages.paper', ['submission' => $this->record->id]), true)
                ->label(function () {
                    if ($this->record->isPublished()) {
                        return 'View';
                    }

                    if (auth()->user()->can('editing', $this->record)) {
                        return 'Preview';
                    }
                })
                ->visible(
                    fn(): bool => ($this->record->isPublished() || auth()->user()->can('editing', $this->record)) && $this->record->proceeding
                ),
            Action::make('assign_proceeding')
                ->label('Publish Now')
                ->modalHeading('Assign Proceeding for publication')
                ->visible(fn() => !$this->record->proceeding && $this->record->stage == SubmissionStage::Editing)
                ->modalWidth(MaxWidth::ExtraLarge)
                ->form(SubmissionProceeding::getFormAssignProceeding($this->record))
                ->action(function (array $data) {
                    SubmissionProceeding::assignProceeding($this->record, $data);

                    $this->replaceMountedAction('publish');
                    $this->dispatch('refreshSubmissionProceeding');
                }),
            Action::make('publish')
                ->color('primary')
                ->label('Publish Now')
                ->visible(
                    fn(): bool => $this->record->proceeding ? true : false
                )
                ->authorize('publish', $this->record)
                ->successNotificationTitle('Submission published successfully')
                ->mountUsing(function (Form $form) {
                    $mailTemplate = MailTemplate::where('mailable', PublishSubmissionMail::class)->first();
                    $form->fill([
                        'email' => $this->record->user->email,
                        'subject' => $mailTemplate ? $mailTemplate->subject : '',
                        'message' => $mailTemplate ? $mailTemplate->html_template : '',
                    ]);
                })
                ->form([
                    Fieldset::make('Notification')
                        ->columns(1)
                        ->schema([
                            TextInput::make('email')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('subject')
                                ->required(),
                            TinyEditor::make('message')
                                ->profile('email')
                                ->minHeight(300),
                            Checkbox::make('do-not-notify-author')
                                ->label("Don't Send Notification to Author"),
                        ]),
                ])
                ->action(function (Action $action, array $data) {
                    $this->record->state()->publish();

                    if (!$data['do-not-notify-author']) {
                        try {
                            Mail::to($this->record->user->email)
                                ->send(
                                    (new PublishSubmissionMail($this->record))
                                        ->subjectUsing($data['subject'])
                                        ->contentUsing($data['message'])
                                );
                        } catch (\Exception $e) {
                            $action->failureNotificationTitle('Failed to send notification to author');
                            $action->failure();
                        }
                    }
                    $action->successRedirectUrl(
                        SubmissionResource::getUrl('view', [
                            'record' => $this->record->getKey(),
                        ])
                    );
                    $action->success();
                }),
            Action::make('unpublish')
                ->icon('lineawesome-calendar-times-solid')
                ->color('danger')
                ->authorize('unpublish', $this->record)
                ->requiresConfirmation()
                ->successNotificationTitle('Submission unpublished')
                ->action(function (Action $action) {
                    $this->record->state()->unpublish();

                    $action->successRedirectUrl(
                        static::getResource()::getUrl('view', [
                            'record' => $this->record,
                        ])
                    );

                    $action->success();
                }),
            Action::make('request_withdraw')
                ->outlined()
                ->color('danger')
                ->authorize('requestWithdraw', $this->record)
                ->label('Request for Withdrawal')
                ->icon('lineawesome-times-circle-solid')
                ->form([
                    Textarea::make('reason')
                        ->required()
                        ->placeholder('Reason for withdrawal')
                        ->label('Reason'),
                ])
                ->requiresConfirmation()
                ->successNotificationTitle('Withdraw Requested, Please wait for editor to approve')
                ->action(function (Action $action, array $data) {
                    RequestWithdrawalAction::run(
                        $this->record,
                        $data['reason']
                    );

                    try {
                        // Currently using admin, next is admin removed only managers
                        User::whereHas(
                            'roles',
                            fn($query) => $query->whereIn('name', [UserRole::Admin->value, UserRole::ConferenceManager->value])
                        )
                            ->get()
                            ->each(
                                fn($manager) => $manager->notify(new SubmissionWithdrawRequested($this->record))
                            );

                        $this
                            ->record
                            ->getEditors()
                            ->each(function (User $editor) {
                                $editor->notify(new SubmissionWithdrawRequested($this->record));
                            });
                    } catch (\Exception $e) {
                        $action->failureNotificationTitle('Failed to send notification');
                        $action->failure();
                    }

                    $action->successRedirectUrl(
                        SubmissionResource::getUrl('view', [
                            'record' => $this->record,
                        ]),
                    );
                    $action->success();
                })
                ->modalWidth('xl'),
            Action::make('withdraw')
                ->outlined()
                ->color('danger')
                ->extraAttributes(function (Action $action) {
                    if (filled($this->record->withdrawn_reason)) {
                        $attributeValue = '$nextTick(() => { $wire.mountAction(\'' . $action->getName() . '\') })';

                        return [
                            'x-init' => new HtmlString($attributeValue),
                        ];
                    }

                    return [];
                })
                ->authorize('withdraw', $this->record)
                ->mountUsing(function (Form $form) {
                    $form->fill([
                        'reason' => $this->record->withdrawn_reason,
                    ]);
                })
                ->form([
                    Textarea::make('reason')
                        ->readonly()
                        ->placeholder('Reason for withdrawal')
                        ->label('Reason'),
                ])
                ->requiresConfirmation()
                ->modalHeading(function () {
                    return $this->record->user->fullName . ' has requested to withdraw this submission.';
                })
                ->modalDescription("You can either reject the request or accept it, remember it can't be undone.")
                ->modalCancelActionLabel('Ignore')
                ->modalSubmitActionLabel('Withdraw')
                ->successNotificationTitle('Withdrawn')
                ->extraModalFooterActions([
                    Action::make('reject')
                        ->color('warning')
                        ->outlined()
                        ->action(function (Action $action) {
                            CancelWithdrawalAction::run($this->record);
                            $action->successRedirectUrl(
                                SubmissionResource::getUrl('view', [
                                    'record' => $this->record,
                                ]),
                            );
                            $action->successNotificationTitle('Withdrawal request rejected');
                            $action->success();
                        }),
                ])
                ->action(function (Action $action) {
                    AcceptWithdrawalAction::run($this->record);
                    try {
                        $this->record->user->notify(
                            new SubmissionWithdrawn($this->record)
                        );
                    } catch (\Exception $e) {
                        $action->failureNotificationTitle('Failed to send notification');
                        $action->failure();
                    }
                    $action->successRedirectUrl(
                        SubmissionResource::getUrl('view', [
                            'record' => $this->record,
                        ]),
                    );
                    $action->success();
                })
                ->modalWidth('2xl'),
            Action::make('activity-log')
                ->hidden(
                    fn(): bool => $this->record->stage == SubmissionStage::Wizard
                )
                ->outlined()
                ->icon('lineawesome-history-solid')
                ->modalHeading('Activity Log')
                ->modalDescription('This is the activity log of this submission, it contains all the changes that has been made to this submission.')
                ->modalWidth('5xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->infolist(function () {
                    return [
                        LivewireEntry::make('activites-table')
                            ->livewire(ActivityLogList::class, [
                                'submission' => $this->record,
                                'lazy' => true,
                            ]),
                    ];
                }),
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        $badgeHtml = '<div class="flex items-center gap-x-2">';

        $badgeHtml .= match ($this->record->status) {
            SubmissionStatus::Incomplete => '<x-filament::badge color="gray" class="w-fit">' . SubmissionStatus::Incomplete->value . '</x-filament::badge>',
            SubmissionStatus::Queued => '<x-filament::badge color="primary" class="w-fit">' . SubmissionStatus::Queued->value . '</x-filament::badge>',
            SubmissionStatus::OnReview => '<x-filament::badge color="warning" class="w-fit">' . SubmissionStatus::OnReview->value . '</x-filament::badge>',
            SubmissionStatus::Published => '<x-filament::badge color="success" class="w-fit">' . SubmissionStatus::Published->value . '</x-filament::badge>',
            SubmissionStatus::Editing => '<x-filament::badge color="info" class="w-fit">' . SubmissionStatus::Editing->value . '</x-filament::badge>',
            SubmissionStatus::Declined => '<x-filament::badge color="danger" class="w-fit">' . SubmissionStatus::Declined->value . '</x-filament::badge>',
            SubmissionStatus::Withdrawn => '<x-filament::badge color="danger" class="w-fit">' . SubmissionStatus::Withdrawn->value . '</x-filament::badge>',
            default => null,
        };

        $badgeHtml .= '</div>';

        return new HtmlString(
            BladeCompiler::render($badgeHtml)
        );
    }

    public function getHeading(): string
    {
        return $this->record->getMeta('title');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                HorizontalTabs::make()
                    // ->persistTabInQueryString('tab')
                    ->contained(false)
                    ->tabs([
                        HorizontalTab::make('Workflow')
                            ->schema([
                                Tabs::make()
                                    ->activeTab(function () {
                                        return match ($this->record->stage) {
                                            SubmissionStage::CallforAbstract => 1,
                                            SubmissionStage::PeerReview => 2,
                                            SubmissionStage::Presentation => 3,
                                            SubmissionStage::Editing, SubmissionStage::Proceeding => 4,
                                            default => null,
                                        };
                                    })
                                    ->sticky()
                                    ->tabs([
                                        Tab::make('Call for Abstract')
                                            ->icon('heroicon-o-information-circle')
                                            ->schema([
                                                LivewireEntry::make('call-for-abstract')
                                                    ->livewire(CallforAbstract::class, [
                                                        'submission' => $this->record,
                                                    ]),
                                            ]),
                                        Tab::make('Peer Review')
                                            ->icon('iconpark-checklist-o')
                                            ->schema([
                                                LivewireEntry::make('peer-review')
                                                    ->livewire(PeerReview::class, [
                                                        'submission' => $this->record,
                                                    ]),
                                            ]),
                                        Tab::make('Presentation')
                                            ->icon('heroicon-o-presentation-chart-bar')
                                            ->schema([
                                                LivewireEntry::make('presentation')
                                                    ->livewire(Presentation::class, [
                                                        'submission' => $this->record,
                                                    ]),
                                            ]),
                                        Tab::make('Editing')
                                            ->icon('heroicon-o-pencil')
                                            ->schema([
                                                LivewireEntry::make('editing')
                                                    ->livewire(Editing::class, [
                                                        'submission' => $this->record,
                                                    ]),
                                            ]),
                                    ])
                                    ->maxWidth('full'),
                            ]),
                        HorizontalTab::make('Publication')
                            ->extraAttributes([
                                'x-on:open-publication-tab.window' => new HtmlString('tab = \'-publication-tab\''),
                            ])
                            ->schema([
                                ShoutEntry::make('can-not-edit')
                                    ->type('warning')
                                    ->color('warning')
                                    ->visible(
                                        fn(): bool => $this->record->isPublished()
                                    )
                                    ->content("You can't edit this submission because it is already published."),
                                Tabs::make()
                                    ->verticalSpace('space-y-2')
                                    // ->persistTabInQueryString('ptab') // ptab shorten of publication-tab
                                    ->tabs([
                                        Tab::make('Detail')
                                            ->icon('heroicon-o-information-circle')
                                            ->schema([
                                                LivewireEntry::make('detail-form')
                                                    ->livewire(Detail::class, [
                                                        'submission' => $this->record,
                                                    ]),
                                            ]),
                                        Tab::make('Contributors')
                                            ->icon('heroicon-o-user-group')
                                            ->schema([
                                                LivewireEntry::make('contributors')
                                                    ->livewire(ContributorList::class, [
                                                        'submission' => $this->record,
                                                        'viewOnly' => !auth()->user()->can('editing', $this->record),
                                                    ]),
                                            ]),
                                        Tab::make('Galleys')
                                            ->icon('heroicon-o-document-text')
                                            ->schema([
                                                LivewireEntry::make('galleys')
                                                    ->livewire(GalleyList::class, [
                                                        'submission' => $this->record,
                                                        'viewOnly' => !auth()->user()->can('editing', $this->record),
                                                    ]),
                                            ]),
                                        Tab::make('Proceeding')
                                            ->icon('heroicon-o-book-open')
                                            ->schema([
                                                LivewireEntry::make('proceeding')
                                                    ->livewire(SubmissionProceeding::class, [
                                                        'submission' => $this->record,
                                                    ]),
                                            ]),
                                        Tab::make('References')
                                            ->icon('iconpark-list')
                                            ->schema([
                                                LivewireEntry::make('references')
                                                    ->livewire(References::class, [
                                                        'submission' => $this->record,
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function getTitle(): string
    {
        return $this->record->stage == SubmissionStage::Wizard ? 'Submission Wizard' : 'Submission';
    }
}
