<?php

namespace App\Panel\ScheduledConference\Resources\SubmissionResource\Pages;

use App\Actions\Submissions\SubmissionCreateAction;
use App\Models\Enums\UserRole;
use App\Models\Submission;
use App\Models\Timeline;
use App\Models\Track;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    protected function getViewData(): array
    {
        return [
            'isOpen' => Timeline::isSubmissionOpen(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('before_you_begin')
                    ->label(__('general.before_you_begin'))
                    ->extraAttributes(['class' => 'prose prose-sm max-w-none'])
                    ->visible(fn () => app()->getCurrentScheduledConference()->getMeta('before_you_begin') !== null)
                    ->content(fn () => new HtmlString(app()->getCurrentScheduledConference()->getMeta('before_you_begin'))),
                TextInput::make('meta.title')
                    ->required(),
                Radio::make('track_id')
                    ->label(__('general.track'))
                    ->required()
                    ->visible(fn () => Track::count() > 1)
                    ->options(fn () => Track::active()->get()->pluck('title', 'id'))
                    ->reactive(),
                Placeholder::make('track_policy')
                    ->extraAttributes(['class' => 'prose prose-sm max-w-none'])
                    ->visible(function (Get $get) {
                        if (! $get('track_id')) {
                            return false;
                        }

                        $track = Track::find($get('track_id'));
                        if ($track->getMeta('policy') === null) {
                            return false;
                        }

                        return true;
                    })
                    ->label(function (Get $get) {
                        if (! $get('track_id')) {
                            return '';
                        }

                        return Track::find($get('track_id'))->title;
                    })
                    ->content(fn (Get $get) => $get('track_id') ? new HtmlString(Track::find($get('track_id'))->getMeta('policy')) : ''),
                Fieldset::make(__('general.submission_checklist'))
                    ->columns(1)
                    ->schema([
                        Placeholder::make('submission_checklist')
                            ->hiddenLabel()
                            ->extraAttributes(['class' => 'prose prose-sm'])
                            ->visible(fn () => app()->getCurrentScheduledConference()->getMeta('submission_checklist') !== null)
                            ->content(fn () => new HtmlString(app()->getCurrentScheduledConference()->getMeta('submission_checklist'))),
                        Checkbox::make('submissionRequirements')
                            ->required()
                            ->label(__('general.submission_meets_all_of_requirements')),
                    ]),
                Section::make(__('general.privacy_consent'))
                    ->schema([
                        Checkbox::make('privacy_consent')
                            ->inline()
                            ->required()
                            ->label(__('general.agree_to_my_collected_stored_to_privacy_statement')),
                    ]),
            ])
            ->model(Submission::class)
            ->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState();

        if (! auth()->user()->roles->isEmpty()) {
            auth()->user()->assignRole(UserRole::Author);
        }

        $submission = SubmissionCreateAction::run($data);

        return redirect()->to(SubmissionResource::getUrl('view', [$submission->id]));
    }
}
