<?php

namespace App\Panel\Conference\Livewire;

use App\Actions\Conferences\ConferenceUpdateAction;
use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use App\Forms\Components\CssFileUpload;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Forms\Components\TinyEditor;
use Stevebauman\Purify\Facades\Purify;

class SetupSetting extends Component implements HasForms
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
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->label(__('general.logo'))
                            ->collection('logo')
                            ->image()
                            ->imageResizeUpscale(false)
                            ->conversion('thumb'),
                        SpatieMediaLibraryFileUpload::make('thumbnail')
                            ->label(__('general.conference_thumbnail'))
                            ->collection('thumbnail')
                            ->helperText(__('general.image_representation_of_the_serie_will_uses'))
                            ->image()
                            ->conversion('thumb'),
                        TinyEditor::make('meta.page_footer')
                            ->label(__('general.page_footer'))
                            ->profile('advanced')
                            ->minHeight(300)
                            ->dehydrateStateUsing(fn (?string $state) => Purify::clean($state)),
                    ]),

                Actions::make([
                    Action::make('save')
                        ->label(__('general.save'))
                        ->successNotificationTitle(__('general.saved'))
                        ->failureNotificationTitle(__('general.data_could_not_saved.'))
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
