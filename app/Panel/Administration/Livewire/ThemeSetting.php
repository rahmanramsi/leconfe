<?php

namespace App\Panel\Administration\Livewire;


use App\Actions\Conferences\ConferenceUpdateAction;
use App\Actions\Site\SiteUpdateAction;
use App\Forms\Components\CssFileUpload;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ThemeSetting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount(): void
    {
        $site = app()->getSite();

        $this->form->fill([
            ...$site->attributesToArray(),
            'meta' => $site->getAllMeta(),
        ]);
    }

    public function render()
    {
        return view('forms.form');
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(app()->getSite())
            ->schema([
                Section::make()
                    ->schema([
                        ColorPicker::make('meta.appearance_color')
                            ->regex('/^#?(([a-f0-9]{3}){1,2})$/i')
                            ->label(__('general.appearance_color')),
                        CssFileUpload::make('styleSheet')
                            ->label(__('general.custom_stylesheet'))
                            ->collection('styleSheet')
                            ->getUploadedFileNameForStorageUsing(static function (BaseFileUpload $component, TemporaryUploadedFile $file) {
                                return Str::random().'.css';
                            })
                            ->acceptedFileTypes(['text/css'])
                            ->columnSpan([
                                'xl' => 1,
                                'sm' => 2,
                            ]),
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label(__('general.save'))
                        ->successNotificationTitle(__('general.saved'))
                        ->failureNotificationTitle(__('general.data_could_not_saved'))
                        ->action(function (Action $action) {
                            $data = $this->form->getState();
                            try {
                                SiteUpdateAction::run($data);
                                $action->sendSuccessNotification();
                            } catch (\Throwable $th) {
                                throw $th;
                                $action->sendFailureNotification();
                            }
                        }),
                ])->alignLeft(),

            ])
            ->statePath('formData');
    }
}
