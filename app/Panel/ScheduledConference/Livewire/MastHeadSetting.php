<?php

namespace App\Panel\ScheduledConference\Livewire;


use App\Actions\Conferences\ConferenceUpdateAction;
use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use App\Forms\Components\CssFileUpload;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Forms\Components\TinyEditor;
use Filament\Forms\Components\Textarea;
use Squire\Models\Country;

class MastHeadSetting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount(): void
    {
        $scheduledConference = app()->getCurrentScheduledConference();

        $this->form->fill([
            ...$scheduledConference->attributesToArray(),
            'meta' => $scheduledConference->getAllMeta(),
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
                    ->schema([
                        Section::make('Scheduled Conference Identity')
                            ->description('Information about the scheduled conference')
                            ->aside()
                            ->schema([
                                TextInput::make('title')
                                    ->label('Title')
                                    ->autofocus()
                                    ->autocomplete()
                                    ->required()
                                    ->placeholder('Enter the title of the scheduled conference'),
                                Textarea::make('meta.description')
                                    ->rows(3)
                                    ->autosize()
                                    ->columnSpanFull()
                                    ->hint('Recommended length: 50-160 characters')
                                    ->helperText('A short description of the website. This will used to help search engines understand the website.'),
                                TextInput::make('meta.acronym')
                                    ->rule('alpha_dash')
                                    ->helperText('The popularly known as or jargon name (e.g. SIGGRAPH for "Special Interest Group on Computer Graphics"). Authors commonly cite the conference acronym rather than the full conference or proceedings name, so it is best to fill this element when it is available.'),
                                TextInput::make('meta.theme')
                                    ->helperText('The theme is the slogan or special emphasis of a conference in a particular year. It differs from the subject of a conference in that the subject is stable over the years while the theme may vary from year to year. For example, the American Society for Information Science and Technology conference theme was "Knowledge: Creation, Organization and Use" in 1999 and "Defining Information Architecture" in 2000.')
                                    ->columnSpanFull(),
                                TextInput::make('meta.location')
                                    ->helperText('The location of the conference. The city, state, province or country of the conference may be provided as appropriate')
                            ]),
                        Section::make('Publishing Details')
                            ->description('These details may be included in metadata provided to third-party archival bodies.')
                            ->aside()
                            ->schema([
                                Select::make('meta.publisher_location')
                                    ->label('Country')
                                    ->placeholder('Select a country')
                                    ->searchable()
                                    ->options(fn () => Country::all()->mapWithKeys(fn ($country) => [$country->name => $country->flag . ' ' . $country->name]))
                                    ->optionsLimit(250),
                                TextInput::make('meta.publisher_name')
                                    ->label('Publisher'),
                                TextInput::make('meta.publisher_url')
                                    ->url()
                                    ->validationMessages([
                                        'url' => 'The URL must be a valid URL.'
                                    ])
                                    ->label('URL')
                            ]),
                        Section::make('Key Information')
                            ->description('Provide a short description of your conference and identify editors, managing directors and other members of your editorial team.')
                            ->aside()
                            ->schema([
                                TinyEditor::make('meta.summary')
                                    ->label('Conference Summary'),
                                TinyEditor::make('meta.editorial_team')
                                    ->label('Editorial Team')
                                    ->profile('basic')
                                    ->minHeight(100),

                            ]),
                        Section::make('Description')
                            ->aside()
                            ->description('Include any information about your conference which may be of interest to readers, authors or reviewers.')
                            ->schema([
                                TinyEditor::make('meta.about')
                                    ->label('About the Conference')
                                    ->profile('basic'),
                            ])
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
                            }
                        }),
                ])->alignLeft(),

            ])
            ->statePath('formData');
    }
}
