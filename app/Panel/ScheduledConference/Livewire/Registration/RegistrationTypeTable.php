<?php

namespace App\Panel\ScheduledConference\Livewire\Registration;

use Filament\Forms\Get;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RegistrationType;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use App\Forms\Components\TinyEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Actions\RegistrationTypes\RegistrationTypeCreateAction;
use App\Actions\RegistrationTypes\RegistrationTypeDeleteAction;
use App\Actions\RegistrationTypes\RegistrationTypeUpdateAction;
use App\Facades\Setting;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentManualTable;
use Filament\Forms\Components\Textarea;

class RegistrationTypeTable extends Component implements HasTable, HasForms
{
    use InteractsWithForms, InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        TextInput::make('type')
                            ->label(__('general.name'))
                            ->placeholder(__('general.input_type_name'))
                            ->required()
                            ->columnSpan(3)
                            ->unique(
                                ignorable: fn() => $form->getRecord(),
                                modifyRuleUsing: fn(Unique $rule) => $rule->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId()),
                            ),
                        TextInput::make('quota')
                            ->label(__('general.participant_quota'))
                            ->placeholder(__('general.input_quota'))
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),
                Select::make('level')
                    ->label(__('general.level'))
                    ->options(RegistrationType::getLevelOptions())
                    ->required(),
                Textarea::make('meta.description')
                    ->label(__('general.description'))
                    ->autosize()
                    ->placeholder(__('general.input_description')),
                Checkbox::make('free')
                    ->label(__('general.set_as_free'))
                    ->formatStateUsing(fn($record) => isset($record->cost) ? $record->cost == 0 : false)
                    ->live(),
                Fieldset::make('Registration Cost')
                    ->label(__('general.registration_cost'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('currency')
                                    ->label(__('general.currency'))
                                    ->formatStateUsing(fn($state) => ($state !== null) ? ($state !== 'free' ? $state : null) : null)
                                    ->options(PaymentManualTable::getCurrencyOptions())
                                    ->searchable()
                                    ->columnSpan(2)
                                    ->required(),
                                TextInput::make('cost')
                                    ->label(__('general.price'))
                                    ->placeholder(__('general.enter_registration_cost'))
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(2)
                                    ->rules(['gte:1']),
                            ])
                    ])
                    ->visible(fn(Get $get) => !$get('free'))
                    ->columns(1),
                Grid::make(2)
                    ->schema([
                        DatePicker::make('opened_at')
                            ->label(__('general.opened_at'))
                            ->placeholder(__('general.select_type_opened_date'))
                            ->prefixIcon('heroicon-m-calendar-days')
                            ->required()
                            ->before('closed_at'),
                        DatePicker::make('closed_at')
                            ->label(__('general.closed_at'))
                            ->placeholder(__('general.select_type_closed_date'))
                            ->prefixIcon('heroicon-m-calendar-days')
                            ->required()
                            ->after('opened_at'),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RegistrationType::query()
                    ->with('meta')
            )
            ->heading(__('general.type'))
            ->reorderable('order_column')
            ->headerActions([
                CreateAction::make()
                    ->label(__('general.add_type'))
                    ->modalHeading(__('general.create_type'))
                    ->modalWidth('4xl')
                    ->model(RegistrationType::class)
                    ->form(fn(Form $form) => $this->form($form))
                    ->mutateFormDataUsing(function ($data) {
                        if ($data['free']) {
                            $data['cost'] = 0;
                            $data['currency'] = 'free';
                        }
                        return $data;
                    })
                    ->using(fn(array $data) => RegistrationTypeCreateAction::run($data))
                    ->authorize('RegistrationSetting:create'),
            ])
            ->columns([
                TextColumn::make('type')
                    ->label(__('general.name'))
                    ->description(function (RegistrationType $record) {
                        $description = '';
                        if ($record->opened_at) {
                            $description .= $record->opened_at->format(Setting::get('format_date'));
                        }

                        if ($record->opened_at && $record->closed_at) {
                            $description .= ' - ' . $record->closed_at->format(Setting::get('format_date'));
                        }

                        return $description;
                    }),
                TextColumn::make('quota')
                    ->label(__('general.quota'))
                    ->formatStateUsing(fn(Model $record) => $record->getPaidParticipantCount() . '/' . $record->quota)
                    ->badge()
                    ->color(Color::Blue),
                TextColumn::make('price')
                    ->getStateUsing(fn(Model $record) => ($record->cost === 0) ? 'Free' : moneyOrFree($record->cost, $record->currency, true)),
                ToggleColumn::make('active')
                    ->label(__('general.active'))
                    ->onColor(Color::Green)
                    ->offColor(Color::Red),
            ])
            ->emptyStateHeading(__('general.type_are_empty'))
            ->emptyStateDescription(__('general.create_a_type_to_get_started'))
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->form(fn(Form $form) => $this->form($form))
                        ->using(fn(Model $record, array $data) => RegistrationTypeUpdateAction::run($record, $data))
                        ->mutateRecordDataUsing(function ($record, $data) {
                            $data['meta'] = $record->getAllMeta();
                            return $data;
                        })
                        ->mutateFormDataUsing(function ($data) {
                            if ($data['free']) {
                                $data['cost'] = 0;
                                $data['currency'] = 'free';
                            }
                            return $data;
                        })
                        ->authorize('RegistrationSetting:edit'),
                    DeleteAction::make()
                        ->using(fn(Model $record) => RegistrationTypeDeleteAction::run($record))
                        ->authorize('RegistrationSetting:delete'),
                ])
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('RegistrationSetting:delete'),
            ])
            ->paginated(false);
    }

    public function render()
    {
        return view('tables.table');
    }
}
