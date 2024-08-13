<?php

namespace App\Panel\ScheduledConference\Livewire;

use Livewire\Component;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Jackiedo\Timezonelist\Facades\Timezonelist;
use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;

class SetupSetting extends Component implements HasForms
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
                        Select::make('meta.timezone')
                            ->options([
                                ...Timezonelist::toArray(false)
                            ])
                            ->selectablePlaceholder(false)
                            ->searchable()
                            ->required()
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label(__('general.save'))
                        ->successNotificationTitle(__('general.saved'))
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
