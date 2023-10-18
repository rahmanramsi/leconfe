<?php

namespace App\Administration\Livewire;

use Livewire\Component;
use Filament\Forms\Form;
use App\Actions\Site\SiteUpdateAction;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class InformationSetting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount()
    {
        $this->form->fill([
            'meta' => app()->getSite()->getAllMeta()->toArray(),
        ]);
    }

    public function render()
    {
        return view('administration.livewire.form');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('meta.name')
                            ->label('Website Name')
                            ->required(),
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('logo')
                            ->model(app()->getSite())
                            ->image()
                            ->imageResizeUpscale(false)
                            ->conversion('thumb')
                            ->columnSpan([
                                'sm' => 2,
                            ]),
                        TinyEditor::make('meta.page_footer')
                            ->minHeight(300)
                            ->columnSpan([
                                'sm' => 2,
                            ]),
                    ])
                    ->columns(2),
                Actions::make([
                    Action::make('save')
                        ->successNotificationTitle('Saved!')
                        ->failureNotificationTitle('Failed!')
                        ->action(function (Action $action) {
                            $data = $this->form->getState();
                            try {
                                SiteUpdateAction::run($data);
                                $action->sendSuccessNotification();
                            } catch (\Throwable $th) {
                                $action->sendFailureNotification();
                            }
                        }),
                ]),
            ])
            ->statePath('formData');
    }
}
