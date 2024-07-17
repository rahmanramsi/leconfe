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

class InformationSetting extends Component implements HasForms
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
                        TextInput::make('title')
                            ->label('Scheduled  Conference Title')
                            ->autofocus()
                            ->autocomplete()
                            ->required()
                            ->placeholder('Enter the title of the serie'),
                        Grid::make([
                            'xl' => 2
                        ])
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('thumbnail')
                                    ->collection('thumbnail')
                                    ->helperText('An image representation of the serie that will be used in the list of series.')
                                    ->image()
                                    ->conversion('thumb'),
                                SpatieMediaLibraryFileUpload::make('cover')
                                    ->collection('cover')
                                    ->helperText('Cover image for the serie.')
                                    ->image()
                                    ->conversion('thumb'),
                            ]),
                        Grid::make()
                            ->schema([
                                DatePicker::make('date_start')
                                    ->label('Start Date')
                                    ->placeholder('Enter the start date of the serie')
                                    ->requiredWith('date_end'),
                                DatePicker::make('date_end')
                                    ->label('End Date')
                                    ->afterOrEqual('date_start')
                                    ->requiredWith('date_start')
                                    ->placeholder('Enter the end date of the serie'),
                            ]),
                        Select::make('type')
                            ->required()
                            ->options(ScheduledConferenceType::array()),
                        TinyEditor::make('meta.about')
                            ->label('About Serie')
                            ->minHeight(300),
                        TinyEditor::make('meta.additional_content')
                            ->minHeight(300),
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
