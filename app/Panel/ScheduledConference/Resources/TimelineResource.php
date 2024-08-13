<?php

namespace App\Panel\ScheduledConference\Resources;

use App\Models\Role;
use App\Facades\Setting;
use App\Models\Timeline;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use App\Tables\Columns\IndexColumn;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use App\Forms\Components\TinyEditor;
use App\Models\Session;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\CheckboxList;
use App\Panel\ScheduledConference\Resources\TimelineResource\Pages;

class TimelineResource extends Resource
{
    protected static ?string $model = Timeline::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function getNavigationGroup(): string
    {
        return __('general.conference');
    }

    public static function getModelLabel(): string
    {
        return __('general.timeline');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('general.name'))
                    ->required(),
                Textarea::make('description')
                    ->label(__('general.description'))
                    ->maxLength(255),
                DatePicker::make('date')
                    ->label(__('general.date'))
                    ->required(),
                Checkbox::make('require_attendance')
                    ->helperText(__('general.by_turning_this_on_participants_only_need_attend_here')),
                Select::make('type')
                    ->label(__('general.type'))
                    ->options(Timeline::getTypes())
                    ->helperText(__('general.type_integrates_with_workflow_process'))
                    ->unique(
                        ignorable: fn() => $form->getRecord(),
                        modifyRuleUsing: fn(Unique $rule) => $rule->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId()),
                    )
                    ->native(false),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Timeline::query())
            ->heading(__('general.timeline'))
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')
                    ->label(__('general.date'))
                    ->dateTime(Setting::get('format_date'))
                    ->description(fn (Model $record) => $record->getEarliestTime()->format(Setting::get('format_time')) . ' - ' . $record->getLatestTime()->format(Setting::get('format_time')))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('general.name')),
                TextColumn::make('sessions_count')
                    ->label('Session')
                    ->counts('sessions')
                    ->badge()
                    ->color(Color::Blue)
                    ->alignCenter(),
                IconColumn::make('require_attendance')
                    ->trueIcon('heroicon-o-check-circle')
                    ->trueColor(Color::Green)
                    ->falseIcon('heroicon-o-x-circle')
                    ->falseColor(Color::Red)
                    ->boolean()
                    ->alignCenter(),
                ToggleColumn::make('hide')
                    ->label(__('general.hidden')),
            ])
            ->recordUrl(fn(Model $record) => static::getUrl('session', ['record' => $record]))
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->model(Timeline::class)
                    ->after(function (Model $record, array $data) {
                        $date = $data['date'];
                        foreach($record->sessions as $session) {
                            $session->date = $date;
                            $session->save();
                        }
                        return $data;
                    }),
                ActionGroup::make([
                    Action::make('session')
                        ->label(__('general.details'))
                        ->icon('heroicon-m-calendar-days')
                        ->color(Color::Blue)
                        ->url(fn(Model $record) => static::getUrl('session', ['record' => $record]))
                        ->authorize(fn(Model $record) => auth()->user()->can('view', $record) && auth()->user()->can('viewAny', Session::class)),
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
            'all-session' => Pages\ListAllSession::route('/session'),
            'session' => Pages\ListSession::route('/{record}/session'),
        ];
    }
}
