<?php

namespace App\Panel\ScheduledConference\Livewire;

use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use App\Facades\Plugin as FacadesPlugin;
use App\Forms\Components\CssFileUpload;
use App\Managers\PluginManager;
use App\Models\Plugin;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

class ThemeSetting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount(): void
    {
        $scheduledConference = app()->getCurrentScheduledConference();
        $activeTheme = $scheduledConference->getMeta('theme');

        $this->form->fill([
            'meta'  => [
                'theme' => $activeTheme,
                'appearance_color' => $scheduledConference->getMeta('appearance_color'),
            ],
            'theme' => FacadesPlugin::getPlugin($activeTheme)?->getFormData() ?? [],
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
                        Select::make('meta.theme')
                            ->reactive()
                            ->options(fn() => Plugin::theme()->enabled()->pluck('name', 'id'))
                            ->afterStateUpdated(function (Get $get, &$livewire): void {
                                if(!$get('meta.theme')) {
                                    return;
                                }

                                $livewire->formData['theme'] = FacadesPlugin::getPlugin($get('meta.theme'))?->getFormData();
                            })
                            ->placeholder('Default'),
                        Grid::make(1)
                            ->visible(fn(Get $get) => !$get('meta.theme'))
                            ->schema([
                                ColorPicker::make('meta.appearance_color')
                                    ->regex('/^#?(([a-f0-9]{3}){1,2})$/i')
                                    ->label(__('general.appearance_color')),
                            ]),
                        Grid::make(1)
                            ->visible(fn(Get $get) => $get('meta.theme'))
                            ->statePath('theme')
                            ->schema(function (Get $get): array {
                                return FacadesPlugin::getPlugin($get('meta.theme'))?->getFormSchema() ?? [];
                            })
                        // CssFileUpload::make('styleSheet')
                        //     ->label(__('general.custom_stylesheet'))
                        //     ->collection('styleSheet')
                        //     ->getUploadedFileNameForStorageUsing(static function (BaseFileUpload $component, TemporaryUploadedFile $file) {
                        //         return Str::random().'.css';
                        //     })
                        //     ->acceptedFileTypes(['text/css'])
                        //     ->columnSpan([
                        //         'xl' => 1,
                        //         'sm' => 2,
                        //     ]),
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label(__('general.save'))
                        ->successNotificationTitle(__('general.saved'))
                        ->failureNotificationTitle(__('general.data_could_not_saved'))
                        ->action(function (Action $action) {
                            $formData = $this->form->getState();
                            try {
                                ScheduledConferenceUpdateAction::run(app()->getCurrentScheduledConference(), Arr::only($formData, ['meta']));

                                $theme = FacadesPlugin::getPlugin($formData['meta']['theme']);
                                $theme?->saveFormData($formData['theme'] ?? []);

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
