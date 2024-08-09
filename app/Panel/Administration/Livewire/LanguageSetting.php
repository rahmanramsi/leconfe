<?php

namespace App\Panel\Administration\Livewire;

use App\Actions\Conferences\ConferenceUpdateAction;
use App\Facades\Setting;
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
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Squire\Models\Country;

class LanguageSetting extends Component implements HasForms
{
	use InteractsWithForms;

	public ?array $formData = [];

	public function mount(): void
	{
		$this->form->fill(Setting::all());
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
						CheckboxList::make('languages')
							->options(config('app.locales'))
							->required(),
						Radio::make('default_language')
							->options(config('app.locales'))
							->required(),
					]),
				Actions::make([
					Action::make('save')
						->label('Save')
						->successNotificationTitle('Saved!')
						->failureNotificationTitle('Data could not be saved.')
						->action(function (Action $action) {
							$formData = $this->form->getState();
                            try {
                                Setting::update($formData);

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
