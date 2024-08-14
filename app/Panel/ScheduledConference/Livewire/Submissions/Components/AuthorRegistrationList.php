<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components;

use Filament\Tables\Table;
use App\Models\RegistrationType;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Colors\Color;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Contracts\Database\Eloquent\Builder;

class AuthorRegistrationList extends \Livewire\Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Registration Type')
            ->query(fn (): Builder => RegistrationType::query()
                ->where('active', true)
                ->where('level', RegistrationType::LEVEL_AUTHOR)
            )
            ->headerActions([
                Action::make('registrationPage')
                    ->label('Registration page')
                    ->url(fn () => route('livewirePageGroup.scheduledConference.pages.participant-registration'))
                    ->link()
            ])
            ->columns([
                TextColumn::make('type')
                    ->label(__('general.type')),
                TextColumn::make('quota')
                    ->label(__('general.quota'))
                    ->formatStateUsing(fn (Model $record) => $record->getPaidParticipantCount() . '/' . $record->quota)
                    ->badge(),
                TextColumn::make('cost')
                    ->label(__('general.cost'))
                    ->formatStateUsing(fn (Model $record) => money($record->cost, $record->currency)),
            ])
            ->actions([
                Action::make('details')
                    ->label(__('general.details'))
                    ->infolist([
                        TextEntry::make('meta.description')
                            ->label('')
                            ->getStateUsing(fn (Model $record) => $record->getMeta('description')),
                    ])
                    ->modalHeading('Description')
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
