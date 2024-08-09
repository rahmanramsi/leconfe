<?php

namespace App\Panel\ScheduledConference\Livewire\Payment;

use Livewire\Component;
use Filament\Forms\Form;
use App\Forms\Components\TinyEditor;
use Filament\Forms\Components\Actions;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use Filament\Forms\Components\Section;

class PaymentSettings extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount(): void
    {
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TinyEditor::make('meta.payment_policy')
                            ->profile('basic')
                            ->minHeight(450)
                            ->disabled(fn () => auth()->user()->cannot('PaymentSetting:update')),
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
                                })
                                ->authorize('PaymentSetting:update'),
                        ])->alignRight(),
                    ])
            ])
            ->statePath('formData');
    }

    public function render()
    {
        return view('forms.form');
    }
}
