<?php

namespace App\Panel\Conference\Livewire;

use App\Actions\Conferences\ConferenceUpdateAction;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use App\Forms\Components\TinyEditor;
use Filament\Forms\Components\Textarea;
use Squire\Models\Country;

class MastHeadSetting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount(): void
    {
        $conference = app()->getCurrentConference();

        $this->form->fill([
            ...$conference->attributesToArray(),
            'meta' => $conference->getAllMeta(),
        ]);
    }

    public function render()
    {
        return view('forms.form');
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(app()->getCurrentConference())
            ->schema([
                Section::make()
                    ->schema([
                        Section::make('Conference Identity')
                            ->description('Information about the scheduled conference')
                            ->aside()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->autofocus()
                                    ->autocomplete()
                                    ->required(),
                                TextInput::make('meta.issn')
                                    ->label('ISSN')
                                    ->helperText('The ISSN of the conference'),
                                Textarea::make('meta.description')
                                    ->rows(3)
                                    ->autosize()
                                    ->columnSpanFull()
                                    ->hint('Recommended length: 50-160 characters')
                                    ->helperText('A short description of the website. This will used to help search engines understand the website.'),
                            ]),
                        Section::make('Key Information')
                            ->description('Provide a short description of your conference and identify editors, managing directors and other members of your editorial team.')
                            ->aside()
                            ->schema([
                                TinyEditor::make('meta.summary')
                                    ->label('Conference Summary'),

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
                            try {
                                ConferenceUpdateAction::run($this->form->getRecord(), $this->form->getState());
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
