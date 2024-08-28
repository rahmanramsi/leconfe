<?php

namespace App\Panel\ScheduledConference\Livewire\Payment;

use Livewire\Component;
use Filament\Forms\Form;
use App\Forms\Components\TinyEditor;
use Filament\Forms\Components\Actions;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use App\Models\Enums\SubmissionStage;
use App\Models\Enums\SubmissionStatus;
use App\Models\Submission;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\HtmlString;
use Illuminate\View\Compilers\BladeCompiler;

class PaymentSetting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill([
            'meta' => app()->getCurrentScheduledConference()->getAllMeta(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Toggle::make('meta.submission_payment')
                            ->label(__('general.enable_submission_payment'))
                            ->helperText(fn () => !app()->getCurrentScheduledConference()->isSubmissionRequirePayment() ? null : new HtmlString("
                                <p style='color: rgb(234 179 8)!important;'>
                                    ".__('general.submission_payment_toggle_warning')."
                                </p>
                            ")), // TODO: change this to text-warning (tailwind wont load)
                        TinyEditor::make('meta.payment_policy')
                            ->label(__('general.payment_policy'))
                            ->plugins('advlist autoresize codesample directionality emoticons fullscreen hr image imagetools link lists media table toc wordcount code')
                            ->toolbar('undo redo removeformat | formatselect fontsizeselect | bold italic | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist | forecolor backcolor | blockquote table hr | image link code')
                            ->minHeight(450),
                    ])
                    ->disabled(fn () =>  auth()->user()->cannot('RegistrationSetting:edit')),
                Actions::make([
                    Action::make('save_changes')
                        ->label(__('general.save_changes'))
                        ->successNotificationTitle(__('general.saved'))
                        ->failureNotificationTitle(__('general.data_could_not_saved'))
                        ->action(function (Action $action) {
                            $formData = $this->form->getState();

                            try {

                                ScheduledConferenceUpdateAction::run(app()->getCurrentScheduledConference(), $formData);

                                Submission::where('stage', SubmissionStage::Payment)
                                    ->orWhere('status', SubmissionStatus::OnPayment)
                                    ->orWhere('status', SubmissionStatus::PaymentDeclined)
                                    ->get()
                                    ->each(function (Submission $submission) {
                                        $submission->update([
                                            'stage' => SubmissionStage::PeerReview,
                                            'status' => SubmissionStatus::OnReview,
                                        ]);
                                    });

                            } catch (\Throwable $th) {

                                $action->failure();
                                throw $th;

                            }

                            $action->success();
                        })
                        ->authorize('RegistrationSetting:edit'),
                ])->alignRight(),
            ])
            ->statePath('formData');
    }

    public function render()
    {
        return view('forms.form');
    }
}
