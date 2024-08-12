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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
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
                        Section::make(__('general.scheduled_conference_identity'))
                            ->description(__('general.information_about_the_scheduled_conference'))
                            ->aside()
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('general.title'))
                                    ->autofocus()
                                    ->autocomplete()
                                    ->required()
                                    ->placeholder(__('general.enter_the_title_of_the_scheduled_conference')),
                                Grid::make()
                                    ->schema([
                                        DatePicker::make('date_start')
                                            ->label(__('general.start_date'))
                                            ->placeholder(__('general.enter_the_start_date_of_the_serie'))
                                            ->requiredWith('date_end'),
                                        DatePicker::make('date_end')
                                            ->label(__('general.end_date'))
                                            ->afterOrEqual('date_start')
                                            ->requiredWith('date_start')
                                            ->placeholder(__('general.enter_the_end_date_of_the_serie')),
                                    ]),
                                Textarea::make('meta.description')
                                    ->label(__('general.description'))
                                    ->rows(3)
                                    ->autosize()
                                    ->columnSpanFull()
                                    ->hint(__('general.recomended_length_50_160'))
                                    ->helperText(__('general.short_description_of_the_website')),
                                TextInput::make('meta.acronym')
                                    ->label(__('general.acronym'))
                                    ->rule('alpha_dash')
                                    ->helperText(__('general.acronym_rather_than_the_full_conference')),
                                TextInput::make('meta.theme')
                                    ->label(__('general.theme'))
                                    ->helperText(__('general.theme_information'))
                                    ->columnSpanFull(),
                                TextInput::make('meta.location')
                                    ->label(__('general.location'))
                                    ->helperText(__('general.location_description'))
                            ]),
                        Section::make(__('general.publishing_details'))
                            ->description(__('general.publishing_detail_included_in_metadata'))
                            ->aside()
                            ->schema([
                                Select::make('meta.publisher_location')
                                    ->label(__('general.country'))
                                    ->placeholder(__('general.select_a_country'))
                                    ->searchable()
                                    ->options(fn () => Country::all()->mapWithKeys(fn ($country) => [$country->name => $country->flag . ' ' . $country->name]))
                                    ->optionsLimit(250),
                                TextInput::make('meta.publisher_name')
                                    ->label(__('general.publisher')),
                                TextInput::make('meta.publisher_url')
                                    ->url()
                                    ->validationMessages([
                                        'url' => __('general.url_must_be_valid')
                                    ])
                                    ->label(__('general.url'))
                            ]),
                        Section::make(__('general.key_information'))
                            ->description(__('general.key_information_pricide_a_short_description'))
                            ->aside()
                            ->schema([
                                TinyEditor::make('meta.summary')
                                    ->label(__('general.conference_summary')),
                                TinyEditor::make('meta.editorial_team')
                                    ->label(__('general.editorial_team'))
                                    ->profile('basic')
                                    ->minHeight(100),

                            ]),
                        Section::make(__('general.description'))
                            ->aside()
                            ->description(__('general.include_about_your_conference'))
                            ->schema([
                                TinyEditor::make('meta.about')
                                    ->label(__('general.about_the_scheduled_conference'))
                                    ->profile('basic'),
                            ])
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label(__('general.save'))
                        ->successNotificationTitle(__('general.saved'))
                        ->failureNotificationTitle(__('general.data_could_not_saved'))
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
