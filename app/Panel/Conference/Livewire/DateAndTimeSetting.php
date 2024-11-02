<?php

namespace App\Panel\Conference\Livewire;

use App\Facades\Setting;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class DateAndTimeSetting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount()
    {
        $this->form->fill(Setting::all());
    }

    public function render()
    {
        return view('forms.form');
    }

    public function form(Form $form): Form
    {
        $now = now()->hours(16);

        return $form
            ->statePath('formData')
            ->schema([
                Section::make(__('general.date_and_time_formats'))
                    ->description(new HtmlString(__('general.format_character_dates_and_times')))
                    ->schema([
                        Radio::make('select_format_date')
                            ->label(__('general.date'))
                            ->options(
                                fn () => collect([
                                    'F j, Y',
                                    'F j Y',
                                    'j F Y',
                                    'Y F j',
                                ])
                                    ->mapWithKeys(fn ($format) => [$format => $now->format($format)])
                                    ->put('custom', __('general.custom'))
                            ),
                        TextInput::make('format_date')
                            ->hiddenLabel()
                            ->placeholder(__('general.enter_custom_date_format'))
                            ->required(fn (Get $get) => $get('select_format_date') === 'custom')
                            ->dehydrated()
                            ->dehydrateStateUsing(fn (Get $get, ?string $state) => $get('select_format_date') === 'custom' ? $state : $get('select_format_date')),
                        Radio::make('select_format_time')
                            ->label(__('general.time'))
                            ->options(
                                fn () => collect([
                                    'h:i A',
                                    'g:ia',
                                    'H:i',
                                ])
                                    ->mapWithKeys(fn ($format) => [$format => $now->format($format)])
                                    ->put('custom', __('general.custom'))
                            ),
                        TextInput::make('format_time')
                            ->hiddenLabel()
                            ->placeholder(__('general.enter_custom_time_format'))
                            ->required(fn (Get $get) => $get('select_format_time') === 'custom')
                            ->dehydrated()
                            ->dehydrateStateUsing(fn (Get $get, ?string $state) => $get('select_format_time') === 'custom' ? $state : $get('select_format_time')),
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label(__('general.save'))
                        ->successNotificationTitle(__('general.saved'))
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
            ]);
    }
}
