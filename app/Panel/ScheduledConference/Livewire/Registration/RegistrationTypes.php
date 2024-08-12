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
use App\Panel\ScheduledConference\Livewire\Payment\PaymentManualPage;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentManuals;

class RegistrationTypes extends Component implements HasTable, HasForms
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
                            ignorable: fn () => $form->getRecord(),
                            modifyRuleUsing: fn (Unique $rule) => $rule->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId()),
                        ),
                    TextInput::make('quota')
                        ->label(__('general.participant_quota'))
                        ->placeholder(__('general.input_quota'))
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ]),
            TinyEditor::make('meta.description')
                ->label(__('general.description'))
                ->placeholder(__('general.input_description'))
                ->formatStateUsing(fn (string $operation, $record) => $operation === 'edit' ? $record->getMeta('description') : null)
                ->minHeight(100),
            Checkbox::make('free')
                ->label(__('general.set_as_free'))
                ->formatStateUsing(fn ($record) => isset($record->cost) ? $record->cost == 0 : false)
                ->live(),
            Fieldset::make(__('general.registration_cost'))
                ->schema([
                    Select::make('currency')
                        ->label(__('general.currency'))
                        ->formatStateUsing(fn ($state) => ($state !== null) ? ($state !== 'free' ? $state : null) : null)
                        ->options(PaymentManuals::getCurrencyOptions())
                        ->searchable()
                        ->required()
                        ->live(),
                    Grid::make(4)
                        ->schema([
                            TextInput::make('cost')
                                ->label(__('general.price'))
                                ->placeholder(__('general.enter_registration_cost'))
                                ->numeric()
                                ->required()
                                ->live()
                                ->columnSpan(3)
                                ->rules(['gte:1']),
                            Placeholder::make(__('general.price_preview'))
                                ->content(fn (Get $get) => ($get('currency') !== null && $get('currency') !== 'free' && !empty($get('cost'))) ? money($get('cost'), $get('currency')) : 'N/A')
                        ])
                ])
                ->visible(fn (Get $get) => !$get('free'))
                ->columns(1),
            Grid::make(2)
                ->schema([
                    DatePicker::make('opened_at')
                        ->label(__('general.opened_date'))
                        ->placeholder(__('general.select_type_opened_date'))
                        ->prefixIcon('heroicon-m-calendar-days')
                        ->required()
                        ->before('closed_at'),
                    DatePicker::make('closed_at')
                        ->label(__('general.closed_date'))
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
                    ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
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
                    ->form(fn (Form $form) => $this->form($form))
                    ->mutateFormDataUsing(function ($data) {
                        if ($data['free']) {
                            $data['cost'] = 0;
                            $data['currency'] = 'free';
                        }
                        return $data;
                    })
                    ->using(fn (array $data) => RegistrationTypeCreateAction::run($data))
                    ->authorize('RegistrationSetting:create'),
            ])
            ->columns([
                TextColumn::make('type')
                    ->label(__('general.name')),
                TextColumn::make('quota')
                    ->label(__('general.quota'))
                    ->formatStateUsing(fn (Model $record) => $record->getPaidParticipantCount() . '/' . $record->quota)
                    ->badge()
                    ->color(Color::Blue),
                TextColumn::make('cost')
                    ->label(__('general.cost'))
                    ->formatStateUsing(fn (Model $record) => ($record->cost === 0) ? 'Free' : money($record->cost, $record->currency)),
                TextColumn::make('currency')
                    ->label(__('general.currency'))
                    ->formatStateUsing(fn (Model $record) => ($record->currency === 'free') ? 'None' : '(' . currency($record->currency)->getCurrency() . ') ' . currency($record->currency)->getName())
                    ->wrap(),
                TextColumn::make('opened_at')
                    ->label(__('general.opened_at'))
                    ->date('Y-M-d')
                    ->color(fn (Model $record) => $record->isExpired() ? Color::Red : null),
                TextColumn::make('closed_at')
                    ->label(__('general.closed_at'))
                    ->date('Y-M-d')
                    ->color(fn (Model $record) => $record->isExpired() ? Color::Red : null),
                ToggleColumn::make('active')
                    ->label(__('general.active'))
                    ->onColor(Color::Green)
                    ->offColor(Color::Red),
            ])
            ->emptyStateHeading(__('general.type_are_empty'))
            ->emptyStateDescription(__('general.create_a_type_to_get_started'))
            ->actions([
                EditAction::make()
                    ->form(fn (Form $form) => $this->form($form))
                    ->using(fn (Model $record, array $data) => RegistrationTypeUpdateAction::run($record, $data))
                    ->mutateFormDataUsing(function ($data) {
                        if ($data['free']) {
                            $data['cost'] = 0;
                            $data['currency'] = 'free';
                        }
                        return $data;
                    })
                    ->authorize('RegistrationSetting:edit'),
                ActionGroup::make([
                    DeleteAction::make()
                        ->using(fn (Model $record) => RegistrationTypeDeleteAction::run($record))
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
