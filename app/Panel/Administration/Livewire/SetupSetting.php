<?php

namespace App\Panel\Administration\Livewire;

use App\Actions\Site\SiteUpdateAction;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use App\Forms\Components\TinyEditor;
use Stevebauman\Purify\Facades\Purify;

class SetupSetting extends Component implements HasForms
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
        return view('forms.form');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('meta.name')
                            ->label(__('general.website_name'))
                            ->required(),
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('logo')
                            ->label(__('general.logo'))
                            ->model(app()->getSite())
                            ->image()
                            ->imageResizeUpscale(false)
                            ->conversion('thumb'),
                        Textarea::make('meta.description')
                            ->label(__('general.description'))
                            ->rows(3)
                            ->autosize()
                            ->hint(__('general.recomended_length_50_160'))
                            ->helperText(__('general.short_description_of_the_website')),
                        TinyEditor::make('meta.about')
                            ->label(__('general.about_site'))
                            ->profile('advanced')
                            ->minHeight(300)
                            ->dehydrateStateUsing(fn (?string $state) => Purify::clean($state)),
                        TinyEditor::make('meta.page_footer')
                            ->label(__('general.page_footer'))
                            ->toolbar('bold italic superscript subscript | link | blockquote bullist numlist | image | code')
                            ->plugins('paste link lists image code')
                            ->minHeight(300)
                            ->dehydrateStateUsing(fn (?string $state) => Purify::clean($state)),
                    ])
                    ->columns(1),
                Actions::make([
                    Action::make('save')
                        ->label(__('general.save'))
                        ->successNotificationTitle(__('general.saved'))
                        ->failureNotificationTitle(__('general.failed'))
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
