<?php

namespace App\Panel\ScheduledConference\Livewire\Registration;

use Livewire\Component;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Forms\Components\TinyEditor;

class RegistrationPolicyPage extends Component implements HasForms
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
                Section::make(null)
                    ->schema([
                        TinyEditor::make('meta.registration_policy')
                            ->label('Registration Policy')
                            ->minHeight(300)
                            ->disabled(fn () =>  auth()->user()->cannot('RegistrationSetting:edit')),
                    ]),
                Actions::make([
                    Action::make('Save changes')
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
                        })
                        ->authorize('RegistrationSetting:edit'),
                ])->alignRight()
            ])->statePath('formData');
    }

    public function render()
    {
        return view('panel.scheduledConference.livewire.registration.registration-policy');
    }
}
