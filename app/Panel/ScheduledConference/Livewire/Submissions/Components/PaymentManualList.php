<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components;

use Filament\Tables\Table;
use App\Models\PaymentManual;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Contracts\Database\Eloquent\Builder;

class PaymentManualList extends \Livewire\Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('general.payment_methods'))
            ->query(fn (): Builder => PaymentManual::query()
                ->orderBy('currency', 'ASC')
            )
            ->columns([
                Split::make([
                    TextColumn::make('name')
                        ->label(__('general.name')),
                    TextColumn::make('currency')
                        ->label(__('general.currency'))
                        ->formatStateUsing(fn (Model $record) => currency($record->currency)->getName())
                        ->alignCenter()
                        ->badge(),
                ])
            ])
            ->recordAction('details')
            ->actions([
                Action::make('details')
                    ->label('')
                    ->modalHeading(fn (Model $record) => $record->name . ' ' . __('general.details'))
                    ->infolist([
                        TextEntry::make('detail')
                            ->label('')
                            ->html(),
                    ])
                    ->modalSubmitAction(false)
            ])
            ->emptyStateHeading('')
            ->paginated(false);
    }

    public function render()
    {
        return view('tables.table');
    }
}
