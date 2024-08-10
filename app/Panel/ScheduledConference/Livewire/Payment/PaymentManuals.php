<?php

namespace App\Panel\ScheduledConference\Livewire\Payment;

use Livewire\Component;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PaymentManual;
use Illuminate\Support\Str;
use Squire\Models\Currency;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Forms\Components\TinyEditor;
use App\Models\Announcement;
use App\Panel\ScheduledConference\Livewire\Registration\RegistrationTypePage;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Collection;

class PaymentManuals extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public function mount(): void
    {
    }

    public static function getCurrencyOptions(): Collection
    {
        $currencies = Currency::get();
        $currenciesOptions = $currencies->mapWithKeys(function (?Currency $value, int $key) {
            $currencyCode = Str::upper($value->id);
            $currencyName = $value->name;
            return [$value->id => "($currencyCode) $currencyName"];
        });
        return $currenciesOptions;
    }

    public static function manualPaymentForm(): array
    {
        return [
            TextInput::make('name')
                ->placeholder('Input a name for the payment method..')
                ->required(),
            Select::make('currency')
                ->options(static::getCurrencyOptions())
                ->placeholder('Select payment currency..')
                ->searchable()
                ->required(),
            TinyEditor::make('detail')
                ->placeholder('Input payment details..')
                ->helperText('You may add some intruction here, such as bank account number, account name etc.')
                ->profile('basic')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaymentManual::query()
                    ->orderBy('order_column')
            )
            ->heading('Manual Payment List')
            ->reorderable('order_column')
            ->headerActions([
                CreateAction::make()
                    ->label("Add Manual Payment")
                    ->modalHeading('Create new manual payment')
                    ->modalWidth('4xl')
                    ->model(PaymentManual::class)
                    ->form(static::manualPaymentForm())
                    ->authorize('create', PaymentManual::class)
            ])
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency')
                    ->formatStateUsing(fn ($state) => currency($state))
                    ->badge()
                    ->searchable()
                    ->sortable(),
            ])
            ->groups([
                Group::make('currency')
                    ->getTitleFromRecordUsing(fn (Model $record): string => Str::upper($record->currency))
                    ->label('Currency')
                    ->collapsible()
            ])
            ->emptyStateHeading('Manual payment are empty')
            ->actions([
                EditAction::make()
                    ->form(static::manualPaymentForm())
                    ->authorize(fn (Model $record) => auth()->user()->can('update', $record)),
                ActionGroup::make([
                    DeleteAction::make()
                        ->authorize(fn (Model $record) => auth()->user()->can('delete', $record)),
                ])
            ]);
    }

    public function render()
    {
        return view('tables.table');
    }
}
