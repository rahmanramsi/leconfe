<?php

namespace App\Panel\ScheduledConference\Resources;

use App\Facades\Setting;
use App\Models\Session;
use App\Models\Timeline;
use App\Panel\ScheduledConference\Resources\TimelineResource\Pages;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

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
            ->heading(__('general.timeline'))
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')
                    ->label(__('general.date'))
                    ->dateTime(Setting::get('format_date'))
                    ->description(fn (Model $record) => $record->getEarliestTime()->format(Setting::get('format_time')).' - '.$record->getLatestTime()->format(Setting::get('format_time')))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('general.name')),
                TextColumn::make('sessions_count')
                    ->label('Session')
                    ->counts('sessions')
                    ->badge()
                    ->color(Color::Blue)
                    ->alignCenter(),
                ToggleColumn::make('require_attendance')
                    ->alignCenter(),
                ToggleColumn::make('hide')
                    ->label(__('general.hidden')),
            ])
            ->recordUrl(fn (Model $record) => static::getUrl('session', ['record' => $record]))
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->model(Timeline::class)
                    ->after(function (Model $record, array $data) {
                        $date = Carbon::parse($data['date'], 'UTC');
                        $timezone = app()->getCurrentScheduledConference()->getMeta('timezone');
                        foreach ($record->sessions as $session) {
                            $session->start_at = $session->start_at->copy()->setTimezone($timezone)->setDateFrom($date)->setTimezone('UTC');
                            $session->end_at = $session->end_at->copy()->setTimezone($timezone)->setDateFrom($date)->setTimezone('UTC');
                            $session->save();
                        }
                    }),
                ActionGroup::make([
                    Action::make('session')
                        ->label(__('general.details'))
                        ->icon('heroicon-m-calendar-days')
                        ->color(Color::Blue)
                        ->url(fn (Model $record) => static::getUrl('session', ['record' => $record]))
                        ->authorize(fn (Model $record) => auth()->user()->can('view', $record) && auth()->user()->can('viewAny', Session::class)),
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
            'all-session' => Pages\ListAllSession::route('/sessions'),
            'session' => Pages\ListSession::route('/{record}/sessions'),
        ];
    }
}
