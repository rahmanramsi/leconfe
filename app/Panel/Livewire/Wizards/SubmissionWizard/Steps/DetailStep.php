<?php

namespace App\Panel\Livewire\Wizards\SubmissionWizard\Steps;

use App\Actions\Submissions\SubmissionUpdateAction;
use App\Models\Submission;
use App\Panel\Livewire\Wizards\SubmissionWizard\Contracts\HasWizardStep;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class DetailStep extends Component implements HasForms, HasWizardStep, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public Submission $record;

    public array $meta;

    public string $nextStep = 'upload-files';

    protected $listeners = ['refreshLivewire' => '$refresh'];

    public function mount($record)
    {
        $this->form->fill([
            'meta' => $record->getAllMeta()->toArray(),
        ]);
    }

    public static function getWizardLabel(): string
    {
        return 'Details';
    }

    protected function getFormModel(): string
    {
        return $this->record;
    }

    public function render()
    {
        return view('panel.livewire.wizards.submission-wizard.steps.detail-step');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make([
                Section::make('Submission Details')
                    ->description('Please provide the following details to help us manage your submission in our system.')
                    ->aside()
                    ->schema([
                        Hidden::make('nextStep'),
                        TextInput::make('meta.title')
                            ->required(),
                        SpatieTagsInput::make('meta.keywords')
                            ->splitKeys([',', ' '])
                            ->placeholder('')
                            ->model($this->record)
                            ->type('submissionKeywords'),
                        TinyEditor::make('meta.abstract')
                            ->minHeight(300)
                            ->profile('basic'),
                    ]),
            ]),
        ];
    }

    public function nextStep()
    {
        return Action::make('nextStep')
            ->label("Next")
            ->successNotificationTitle("Saved")
            ->action(function (Action $action) {
                $this->record = SubmissionUpdateAction::run($this->form->getState(), $this->record);
                $this->dispatch('next-wizard-step');
                $action->success();
            });
    }

    public function submit()
    {
        $data = $this->form->getState();

        $this->record = SubmissionUpdateAction::run($data, $this->record);

        $this->dispatch('next-wizard-step');
    }
}
