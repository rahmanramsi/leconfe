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
                ->label(__('general.name'))
                ->placeholder(__('general.input_name_payment_method'))
                ->required(),
            Select::make('currency')
                ->label(__('general.currency'))
                ->options(static::getCurrencyOptions())
                ->placeholder(__('general.select_payment_currency'))
                ->searchable()
                ->required(),
            TinyEditor::make('detail')
                ->placeholder(__('general.input_payment_details'))
                ->hint(__('general.add_instruction_here'))
                ->minHeight(450)
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaymentManual::query()
                    ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            )
            ->heading(__('general.manual_payment_list'))
            ->reorderable('order_column')
            ->headerActions([
                CreateAction::make()
                    ->label(__('general.add_manual_payment'))
                    ->modalHeading(__('general.create_new_manual_payment'))
                    ->modalWidth('4xl')
                    ->model(PaymentManual::class)
                    ->form(static::manualPaymentForm())
                    ->authorize('RegistrationSetting:create')
            ])
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency')
                    ->label(__('general.currency'))
                    ->formatStateUsing(fn ($state) => currency($state))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('detail')
                    ->label(__('general.details'))
                    ->formatStateUsing(fn ($state) => Str::limit(strip_tags($state), 50))
                    ->wrap()
                    ->searchable()
                    ->sortable(),
            ])
            ->groups([
                Group::make('currency')
                    ->getTitleFromRecordUsing(fn (Model $record): string => Str::upper($record->currency))
                    ->getDescriptionFromRecordUsing(fn (Model $record): string => currency($record->currency)->getName())
                    ->label(__('general.code'))
                    ->collapsible()
            ])
            ->emptyStateHeading(__('general.manual_payment_are_empty'))
            ->actions([
                EditAction::make()
                    ->form(static::manualPaymentForm())
                    ->authorize('RegistrationSetting:edit'),
                ActionGroup::make([
                    DeleteAction::make()
                        ->authorize('RegistrationSetting:delete'),
                ])
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('RegistrationSetting:delete'),
            ]);
    }

    public function render()
    {
        return view('tables.table');
    }
}
