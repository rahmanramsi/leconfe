<?php

namespace App\Panel\ScheduledConference\Livewire;

use App\Facades\Setting;
use App\Models\Timeline;
use App\Tables\Columns\IndexColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component;

class TimelineTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public function render()
    {
        return view('tables.table');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Timeline::query())
            ->heading('Timeline')
            ->defaultSort('date')
            ->columns([
                IndexColumn::make('no')
                    ->label('No.'),
                TextColumn::make('name'),
                TextColumn::make('date')
                    ->dateTime(Setting::get('format_date'))
                    ->sortable(),
                ToggleColumn::make('hide')
                    ->label('Hidden'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add new Timeline')
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form(fn (Form $form) => $this->form($form)),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form(fn (Form $form) => $this->form($form)),
                DeleteAction::make(),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->maxLength(255),
                DatePicker::make('date')
                    ->required(),
                Select::make('type')
                    ->options(Timeline::getTypes())
                    ->helperText('Type that integrates with the workflow process.')
                    ->unique(
                        ignorable: fn () => $form->getRecord(),
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId()),
                    ),
            ]);
    }
}
