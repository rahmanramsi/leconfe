<?php

namespace App\Panel\ScheduledConference\Resources\SubmissionResource\Pages;

use App\Actions\Submissions\SubmissionCreateAction;
use App\Models\Timeline;
use App\Panel\ScheduledConference\Livewire\Workflows\Classes\StageManager;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class CreateSubmission extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Make a Submission';

    protected static string $resource = SubmissionResource::class;

    protected static string $view = 'panel.conference.resources.submission-resource.pages.create-submission';

    public $data;

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function getHeading(): string | Htmlable
    {
        return '';
    }


    protected function getViewData(): array
    {
        return [
            'isOpen' => Timeline::isSubmissionOpen(),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Placeholder::make('before_you_begin')
                ->label('Before you begin')
                ->extraAttributes(['class' => 'prose prose-sm'])
                ->visible(fn () => app()->getCurrentScheduledConference()->getMeta('before_you_begin') !== null)
                ->content(fn () => new HtmlString(app()->getCurrentScheduledConference()->getMeta('before_you_begin'))),
            Fieldset::make('Submission Checklist')
                ->columns(1)
                ->schema([
                    Placeholder::make('submission_checklist')
                        ->hiddenLabel()
                        ->extraAttributes(['class' => 'prose prose-sm'])
                        ->visible(fn () => app()->getCurrentScheduledConference()->getMeta('submission_checklist') !== null)
                        ->content(fn () => new HtmlString(app()->getCurrentScheduledConference()->getMeta('submission_checklist'))),
                    Checkbox::make('submissionRequirements')
                        ->required()
                        ->label('Yes, my submission meets all of these requirements.')
                ]),
            TextInput::make('meta.title')
                ->required(),
            Section::make('Privacy Consent')
                ->schema([
                    Checkbox::make('privacy_consent')
                        ->inline()
                        ->required()
                        ->label('Yes, I agree to have my data collected and stored according to the privacy statement.'),
                ]),
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();

        $submission = SubmissionCreateAction::run($data);

        return redirect()->to(SubmissionResource::getUrl('view', [$submission->id]));
    }
}
