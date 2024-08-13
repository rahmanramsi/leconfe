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
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Toggle;

class RegistrationSetting extends Component implements HasForms
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
                        Toggle::make('meta.registration_attend')
                            ->label(__('general.attendance_feature'))
                            ->inline(false)
                            ->disabled(fn () => auth()->user()->cannot('RegistrationSetting:update')),
                        TinyEditor::make('meta.registration_policy')
                            ->label(__('general.registration_policy'))
                            ->profile('basic')
                            ->minHeight(300)
                            ->disabled(fn () => auth()->user()->cannot('RegistrationSetting:update')),
                    ]),
                Actions::make([
                    Action::make('Save changes')
                        ->label(__('general.save_changes'))
                        ->successNotificationTitle(__('general.saved'))
                        ->failureNotificationTitle(__('general.data_could_not_saved'))
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
                        ->authorize('RegistrationSetting:update'),
                ])->alignRight()
            ])->statePath('formData');
    }

    public function render()
    {
        return view('forms.form');
    }
}
