<?php

namespace App\Panel\ScheduledConference\Livewire\Registration;

use Closure;
use Carbon\Carbon;
use Filament\Forms\Get;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Squire\Models\Country;
use Illuminate\Support\Str;
use App\Models\RegistrationType;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Akaunting\Money\View\Components\Currency;
use App\Actions\RegistrationTypes\RegistrationTypeCreateAction;
use App\Actions\RegistrationTypes\RegistrationTypeDeleteAction;
use App\Actions\RegistrationTypes\RegistrationTypeUpdateAction;
use App\Models\Speaker;
use App\Models\User;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Fieldset as ComponentsFieldset;
use App\Forms\Components\TinyEditor;
use Filament\Tables\Actions\ActionGroup;
use Squire\Models\Currency as ModelsCurrency;

class RegistrationTypePage extends Component implements HasTable, HasForms
{
    use
        InteractsWithForms,
        InteractsWithTable;

    public static function countryCurrencySelectOption()
    {
        $currencies = ModelsCurrency::get();
        $currencies_option = $currencies->mapWithKeys(function (?ModelsCurrency $value, int $key) {
            $currency_code = Str::upper($value->id);
            $currency_name = $value->name;
            return [$value->id => "($currency_code) $currency_name"];
        });
        return $currencies_option;
    }
    
    public static function registrationTypeCreateForm(): array
    {
        return [
            Grid::make(4)
                ->schema([
                    TextInput::make('type')
                        ->label('Name')
                        ->placeholder('Input type name..')
                        ->required()
                        ->columnSpan(3)
                        ->rules([
                            fn ($record): Closure => function (string $attribute, $value, Closure $fail) use($record) {
                                if($registration_type = RegistrationType::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->where('type', $value)->first()) {
                                    if($record) {
                                        if($record->id === $registration_type->id)
                                            return;
                                    } 
                                    $fail('Type already exist.');
                                }
                            },
                        ]),
                    TextInput::make('quota')
                        ->label('Participant Quota')
                        ->placeholder('Input quota..')
                        ->numeric()
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
                        ->options(static::countryCurrencySelectOption())
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

        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RegistrationType::query()
                    ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
                    ->with('meta')
            )
            ->heading('Type')
            ->headerActions([
                CreateAction::make()
                    ->label("Add Type")
                    ->modalHeading('Create Type')
                    ->modalWidth('4xl')
                    ->model(RegistrationType::class)
                    ->form(static::registrationTypeCreateForm())
                    ->mutateFormDataUsing(function ($data) {
                        if($data['free'])
                        {
                            $data['cost'] = 0;
                            $data['currency'] = 'free';
                        }
                        return $data;
                    })
                    ->using(fn (array $data) => RegistrationTypeCreateAction::run($data))
                    ->authorize('RegistrationSetting:create'),
            ])
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('type')
                    ->label('Name'),
                TextColumn::make('quota')
                    ->label('Quota')
                    ->badge()
                    ->color(Color::Blue),
                TextColumn::make('cost')
                    ->formatStateUsing(fn (Model $record) => $record->getCost()),
                TextColumn::make('currency')
                    ->label('Currency')
                    ->formatStateUsing(fn (Model $record) => $record->currency === 'free' ? 'None' : currency($record->currency)->getName().' ('.currency($record->currency)->getCurrency().')'),
                TextColumn::make('opened_at')
                    ->date('Y-M-d'),
                TextColumn::make('closed_at')
                    ->date('Y-M-d'),
                ToggleColumn::make('active')
                    ->onColor(Color::Green)
                    ->offColor(Color::Red),
            ])
            ->emptyStateHeading('Type are empty')
            ->emptyStateDescription('Create a Type to get started.')
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->form(static::registrationTypeCreateForm())
                        ->using(fn (Model $record, array $data) => RegistrationTypeUpdateAction::run($record, $data))
                        ->mutateFormDataUsing(function ($data) {
                            if($data['free'])
                            {
                                $data['cost'] = 0;
                                $data['currency'] = 'free';
                            }
                            return $data;
                        })
                        ->authorize('RegistrationSetting:edit'),
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
        return view('panel.scheduledConference.livewire.registration.registration-type');
    }
}
