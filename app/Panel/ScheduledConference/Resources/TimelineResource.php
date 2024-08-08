<?php

namespace App\Panel\ScheduledConference\Resources;

use App\Models\Role;
use App\Facades\Setting;
use App\Models\Timeline;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Tables\Columns\IndexColumn;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\CheckboxList;
use App\Panel\ScheduledConference\Resources\TimelineResource\Pages;
use Filament\Forms\Components\Checkbox;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;

class TimelineResource extends Resource
{
    protected static ?string $model = Timeline::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Conference';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->maxLength(255),
                DatePicker::make('date')
                    ->required(),
                Checkbox::make('requires_attendance')
                    ->helperText('By turning this on, participants only need to attend here.'),
                Select::make('type')
                    ->options(Timeline::getTypes())
                    ->helperText('Type that integrates with the workflow process.')
                    ->unique(
                        ignorable: fn () => $form->getRecord(),
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId()),
                    )
                    ->native(false),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Timeline::query())
            ->heading('Timeline')
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')
                    ->description(function (Model $record) {
                        $time_start = $record->getEarliestTime()->format(Setting::get('format_time'));
                        $time_end = $record->getLatestTime()->format(Setting::get('format_time'));

                        return "$time_start -  $time_end";
                    })
                    ->dateTime(Setting::get('format_date'))
                    ->sortable(),
                TextColumn::make('name'),
                TextColumn::make('agendas_count')
                    ->label('Agenda')
                    ->counts('agendas')
                    ->badge()
                    ->color(Color::Blue)
                    ->alignCenter(),
                IconColumn::make('requires_attendance')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->trueColor(Color::Green)
                    ->falseIcon('heroicon-o-x-circle')
                    ->falseColor(Color::Red)
                    ->alignCenter(),
                ToggleColumn::make('hide')
                    ->label('Hidden'),
            ])
            ->recordUrl(fn (Model $record) => static::getUrl('agenda', ['record' => $record]))
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->model(Timeline::class),
                ActionGroup::make([
                    Action::make('agenda')
                        ->label('Agenda List')
                        ->icon('heroicon-m-calendar-days')
                        ->color(Color::Blue)
                        ->url(fn (Model $record) => static::getUrl('agenda', ['record' => $record])),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTimeline::route('/'),
            'all-agenda' => Pages\ListAllAgenda::route('/agenda'),
            'agenda' => Pages\ListAgenda::route('/{record}/agenda'),
        ];
    }
}
