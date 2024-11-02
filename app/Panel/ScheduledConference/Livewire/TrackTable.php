<?php

namespace App\Panel\ScheduledConference\Livewire;

use App\Actions\Tracks\TrackCreateAction;
use App\Actions\Tracks\TrackUpdateAction;
use App\Forms\Components\TinyEditor;
use App\Models\Track;
use App\Tables\Columns\IndexColumn;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class TrackTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public function render()
    {
        return view('tables.table');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Track::query()->orderBy('order_column'))
            ->heading(__('general.track'))
            ->columns([
                IndexColumn::make('no')
                    ->label('No.'),
                TextColumn::make('title')
                    ->label(__('general.title')),
            ])
            ->reorderable('order_column')
            ->headerActions([
                CreateAction::make()
                    ->label(__('general.new_track'))
                    ->modalWidth(MaxWidth::ThreeExtraLarge)
                    ->form(fn (Form $form) => $this->form($form))
                    ->using(fn (array $data) => TrackCreateAction::run($data)),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth(MaxWidth::ThreeExtraLarge)
                    ->form(fn (Form $form) => $this->form($form))
                    ->mutateRecordDataUsing(function (array $data, Track $record) {
                        $data['meta'] = $record->getAllMeta();

                        return $data;
                    })
                    ->action(fn (Track $record, array $data) => TrackUpdateAction::run($record, $data)),
                DeleteAction::make()
                    ->using(function (Track $record, DeleteAction $action) {
                        try {
                            $record->delete();

                        } catch (\Throwable $th) {

                            $action->failureNotificationTitle($th->getMessage());

                            return false;
                        }

                        return true;
                    }),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('general.track_title'))
                            ->required(),
                        TextInput::make('abbreviation')
                            ->label(__('general.abbreviation'))
                            ->alpha()
                            ->required(),
                    ]),
                TinyEditor::make('meta.policy')
                    ->label(__('general.track_policy'))
                    ->profile('basic'),
            ]);
    }
}
