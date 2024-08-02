<?php

namespace App\Panel\ScheduledConference\Livewire\Registration;

use Filament\Forms\Get;
use Livewire\Component;
use App\Facades\Setting;
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
use App\Panel\ScheduledConference\Livewire\Payment\PaymentManuals;
use App\Panel\ScheduledConference\Livewire\Payment\PaymentManualPage;

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
                        ->label('Name')
                        ->placeholder('Input type name..')
                        ->required()
                        ->columnSpan(3)
                        ->unique(
                            ignorable: fn () => $form->getRecord(),
                            modifyRuleUsing: fn (Unique $rule) => $rule->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId()),
                        ),
                    TextInput::make('quota')
                        ->label('Participant Quota')
                        ->placeholder('Input quota..')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ]),
            TinyEditor::make('meta.description')
                ->label('Description')
                ->placeholder('Input description..')
                ->formatStateUsing(fn (string $operation, $record) => $operation === 'edit' ? $record->getMeta('description') : null)
                ->minHeight(100),
            Checkbox::make('free')
                ->label('Set as free')
                ->formatStateUsing(fn ($record) => isset($record->cost) ? $record->cost == 0 : false)
                ->live(),
            Fieldset::make('Registration Cost')
                ->schema([
                    Select::make('currency')
                        ->label('Currency')
                        ->formatStateUsing(fn ($state) => ($state !== null) ? ($state !== 'free' ? $state : null) : null)
                        ->options(PaymentManuals::getCurrencyOptions())
                        ->searchable()
                        ->required()
                        ->live(),
                    Grid::make(4)
                        ->schema([
                            TextInput::make('cost')
                                ->label('Price')
                                ->placeholder('Enter registration cost..')
                                ->numeric()
                                ->required()
                                ->live()
                                ->columnSpan(3)
                                ->rules(['gte:1']),
                            Placeholder::make('Price Preview')
                                ->content(fn (Get $get) => ($get('currency') !== null && $get('currency') !== 'free' && !empty($get('cost'))) ? money($get('cost'), $get('currency')) : 'N/A')
                        ])
                ])
                ->visible(fn (Get $get) => !$get('free'))
                ->columns(1),
            Grid::make(2)
                ->schema([
                    DatePicker::make('opened_at')
                        ->label('Opened Date')
                        ->placeholder('Select type opened date..')
                        ->prefixIcon('heroicon-m-calendar-days')
                        ->required()
                        ->before('closed_at'),
                    DatePicker::make('closed_at')
                        ->label('Closed Date')
                        ->placeholder('Select type closed date..')
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
                    ->orderBy('order_column')
            )
            ->heading('Type')
            ->reorderable('order_column')
            ->headerActions([
                CreateAction::make()
                    ->label("Add Type")
                    ->modalHeading('Create Type')
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
                    ->label('Name'),
                TextColumn::make('quota')
                    ->label('Quota')
                    ->formatStateUsing(fn (Model $record) => $record->getPaidParticipantCount() . '/' . $record->quota)
                    ->badge()
                    ->color(Color::Blue),
                TextColumn::make('cost')
                    ->formatStateUsing(fn (Model $record) => ($record->cost === 0) ? 'Free' : money($record->cost, $record->currency)),
                TextColumn::make('currency')
                    ->label('Currency')
                    ->formatStateUsing(fn (Model $record) => ($record->currency === 'free') ? 'None' : '(' . currency($record->currency)->getCurrency() . ') ' . currency($record->currency)->getName())
                    ->wrap(),
                TextColumn::make('opened_at')
                    ->date(Setting::get('format_date'))
                    ->color(fn (Model $record) => $record->isExpired() ? Color::Red : null),
                TextColumn::make('closed_at')
                    ->date(Setting::get('format_date'))
                    ->color(fn (Model $record) => $record->isExpired() ? Color::Red : null),
                ToggleColumn::make('active')
                    ->onColor(Color::Green)
                    ->offColor(Color::Red),
            ])
            ->emptyStateHeading('Type are empty')
            ->emptyStateDescription('Create a Type to get started.')
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
