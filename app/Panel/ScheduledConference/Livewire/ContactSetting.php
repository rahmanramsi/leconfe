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
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Forms\Components\TinyEditor;

class ContactSetting extends Component implements HasForms
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
                        Section::make('Principal Contact')
                            ->aside()
                            ->description('Enter contact details, typically for a principal editorship, managing editorship, or administrative staff position, which can be displayed on your publicly accessible website.')
                            ->schema([
                                TextInput::make('meta.principal_contact_name')
                                    ->label('Name')
                                    ->required()
                                    ->autofocus(),
                                TextInput::make('meta.principal_contact_email')
                                    ->label('Email')
                                    ->required()
                                    ->type('email'),
                                TextInput::make('meta.principal_contact_phone')
                                    ->label('Phone')
                                    ->type('tel'),
                                TextInput::make('meta.principal_contact_affiliation')
                                    ->label('Affiliation'),
                            ]),

                        Section::make('Technical Support Contact')
                            ->aside()
                            ->description('A contact person who can assist editors, authors and reviewers with any problems they have submitting, editing, reviewing or publishing material.')
                            ->schema([
                                TextInput::make('meta.support_contact_name')
                                    ->label('Name')
                                    ->required()
                                    ->autofocus(),
                                TextInput::make('meta.support_contact_email')
                                    ->label('Email')
                                    ->required()
                                    ->type('email'),
                                TextInput::make('meta.support_contact_phone')
                                    ->label('Phone')
                                    ->type('tel'),
                            ]),
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
