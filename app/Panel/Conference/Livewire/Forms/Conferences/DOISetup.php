<?php

namespace App\Panel\Conference\Livewire\Forms\Conferences;

use App\Actions\Conferences\ConferenceUpdateAction;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class DOISetup extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill([
            'meta' => app()->getCurrentConference()->getAllMeta(),
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
                        Fieldset::make('DOIs')
                            ->schema([
                                Checkbox::make('meta.doi_enabled')
                                    ->label(__('general.allow_digital_object_dois')),
                            ])
                            ->columns(1),
                        Fieldset::make(__('general.items_with_DOIs'))
                            ->schema([
                                Placeholder::make('items.description')
                                    ->hiddenLabel()
                                    ->content(new HtmlString(__('general.select_which_items_assigned_DOI'))),
                                CheckboxList::make('meta.doi_items')
                                    ->hiddenLabel()
                                    ->options([
                                        'articles' => __('general.articles'),
                                    ]),
                            ])
                            ->columns(1),
                        TextInput::make('meta.doi_prefix')
                            ->label(__('general.doi_prefix'))
                            // ->maxWidth(MaxWidth::Small)
                            ->helperText(new HtmlString(__('general.doi_prefix_assigned_registration')))
                            ->placeholder('10.xxxxx')
                            ->regex('/^10\.\d+$/')
                            ->requiredUnless('meta.doi_enabled', true)
                            ->validationMessages([
                                'regex' => __('general.the_doi_format_validation'),
                                'required_unless' => __('general.the_doi_required_enabled'),
                            ]),
                        Select::make('meta.doi_automatic_assignment')
                            ->label(__('general.automatic_doi_assignment'))
                            ->helperText(new HtmlString(__('general.submission_assigned_doi')))
                            ->placeholder('Never')
                            ->options([
                                'edit_stage' => __('general.option_reaching_editing_stage'),
                                'published' => __('general.option_upon_publication'),
                            ]),
                        Fieldset::make(__('general.doi_format'))
                            ->schema([
                                Radio::make('meta.doi_format')
                                    ->hiddenLabel()
                                    ->options([
                                        'default' => __('general.automatically_generate_unique_suffix'),
                                        'none' => __('general.not_be_automatically_generate_unique_suffix'),
                                    ]),
                            ])
                            ->columns(1),
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label(__('general.save'))
                        ->successNotificationTitle(__('general.saved'))
                        ->failureNotificationTitle(__('general.data_could_not_saved'))
                        ->action(function (Action $action) {
                            $formData = $this->form->getState();
                            try {
                                ConferenceUpdateAction::run(app()->getCurrentConference(), $formData);
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
