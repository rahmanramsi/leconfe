<?php

namespace App\Panel\ScheduledConference\Livewire;


use App\Actions\Conferences\ConferenceUpdateAction;
use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use App\Models\Enums\ScheduledConferenceType;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use App\Forms\Components\TinyEditor;

class AuthorGuidance extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill([
            ...app()->getCurrentScheduledConference()->attributesToArray(),
            'meta' => app()->getCurrentScheduledConference()->getAllMeta(),
        ]);
    }

    public function render()
    {
        return view('forms.form');
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(app()->getCurrentScheduledConference())
            ->schema([
                Section::make()
                    ->columns(1)
                    ->schema([
                        TinyEditor::make('meta.author_guidelines')
                            ->label('Author Guidelines')
                            ->helperText('Provide guidance on anything authors might need to know, such as bibliographic and formatting standards, alongside examples of common citation formats to be used. You may also wish to provide details about the preferred format and subject matter of submissions.')
                            ->toolbar('bold italic superscript subscript | link | blockquote bullist numlist')
                            ->plugins('autoresize link wordcount lists'),
                        TinyEditor::make('meta.before_you_begin')
                            ->label('Before You Begin')
                            ->helperText('Provide a brief explanation of the submission process so that the author knows what to expect.')
                            ->profile('basic'),
                        TinyEditor::make('meta.submission_checklist')
                            ->label('Submission Checklist')
                            ->helperText('Provide a brief explanation of the submission process so that the author knows what to expect.')
                            ->toolbar('bold italic superscript subscript | link | blockquote bullist numlist')
                            ->plugins('autoresize link wordcount lists'),
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label('Save')
                        ->successNotificationTitle('Saved!')
                        ->failureNotificationTitle('Data could not be saved.')
                        ->action(function (Action $action) {
                            $formData = $this->form->getState();
                            try {
                                ScheduledConferenceUpdateAction::run(app()->getCurrentScheduledConference(), $formData);
                                $action->sendSuccessNotification();
                            } catch (\Throwable $th) {
                                $action->sendFailureNotification();
                                throw $th;
                            }
                        }),
                ])->alignLeft(),
            ])
            ->statePath('formData');
    }
}
