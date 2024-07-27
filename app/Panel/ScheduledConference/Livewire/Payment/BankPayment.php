<?php

namespace App\Panel\ScheduledConference\Livewire\Payment;

use Livewire\Component;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Squire\Models\Country;
use App\Models\PaymentBank;
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
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class BankPayment extends Component implements HasForms, HasTable
{
    use
        InteractsWithForms,
        InteractsWithTable;

    public function mount(): void
    {
    }

    public static function getCurrencyOptions()
    {
        $options = [];
        $currencies = Currency::get();
        foreach($currencies as $currency) $options[$currency->id] = '(' . Str::upper($currency->id) . ') ' . $currency->name;

        return $options;
    }

    public static function bankForm(): array
    {
        return [
            TextInput::make('name')
                ->label('Bank name')
                ->placeholder('Input a name for the bank..')
                ->required(),
            Select::make('currency')
                ->options((static::getCurrencyOptions()))
                ->placeholder('Select payment currency..')
                ->searchable()
                ->required(),
            TinyEditor::make('detail')
                ->placeholder('Input payment details..')
                ->minHeight(450)
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaymentBank::query()
                    ->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())
            )
            ->heading('Bank')
            ->headerActions([
                CreateAction::make()
                    ->label("Add Bank")
                    ->modalHeading('Create new bank')
                    ->modalWidth('4xl')
                    ->model(PaymentBank::class)
                    ->form(static::bankForm())
                    ->authorize('PaymentSetting:create')
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
                TextColumn::make('detail')
                    ->formatStateUsing(fn ($state) => Str::limit(strip_tags($state), 50))
                    ->wrap()
                    ->searchable()
                    ->sortable(),
            ])
            ->groups([
                Group::make('currency')
                    ->getTitleFromRecordUsing(fn (Model $record): string => Str::upper($record->currency))
                    ->getDescriptionFromRecordUsing(fn (Model $record): string => currency($record->currency)->getName())
                    ->label('Currency ID')
                    ->collapsible()
            ])
            ->emptyStateHeading('Bank are empty')
            ->actions([
                EditAction::make()
                    ->form(static::bankForm())
                    ->button()
                    ->authorize('PaymentSetting:edit'),
                DeleteAction::make()
                    ->button()
                    ->authorize('PaymentSetting:delete'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('PaymentSetting:delete'),
            ])
            ->paginated(false);
    }

    public function render()
    {
        return view('panel.scheduledConference.livewire.payment.bank');
    }
}
