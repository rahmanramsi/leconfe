<?php

namespace App\Panel\ScheduledConference\Resources\TimelineResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Session;
use Filament\Forms\Get;
use App\Facades\Setting;
use App\Models\Timeline;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Tables\Grouping\Group;
use App\Forms\Components\TinyEditor;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Panel\ScheduledConference\Resources\TimelineResource;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;

class ListAllSession extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $model = Session::class;

    protected static string $resource = TimelineResource::class;

    protected static string $view = 'panel.scheduledConference.resources.timeline-resource.pages.list-session';

    protected static ?string $title = 'Session list';

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
            'List',
            $this->getTitle(),
        ];

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('general.add_session'))
                ->modalHeading(__('general.add_session'))
                ->model(static::$model)
                ->form(fn(Form $form) => $this->form($form))
                // ->mutateFormDataUsing(function (array $data) {
                //     $timezone = app()->getCurrentScheduledConference()->getMeta('timezone');
                //     $timeline = Timeline::where('id', $data['timeline_id'])->first();
                //     if ($timeline) {
                //         $data['start_at'] = (string) Carbon::parse($data['start_at'], $timezone)->setDateFrom($timeline->date);
                //         $data['end_at'] = (string) Carbon::parse($data['end_at'], $timezone)->setDateFrom($timeline->date);
                //         return $data;
                //     }
                // })
                ->authorize('create', Session::class),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('general.session_name'))
                    ->required(),
                TinyEditor::make('public_details')
                    ->minHeight(200)
                    ->profile('basic')
                    ->hint(__('general.detail_that_visible_to_all_user')),
                TinyEditor::make('details')
                    ->minHeight(200)
                    ->profile('basic')
                    ->hint(__('general.detail_that_visible_only_to_participant')),
                Grid::make(2)
                    ->schema([
                        TimePicker::make('start_at')
                            ->label(__("general.time_start"))
                            ->seconds(false)
                            ->native(false)
                            ->dehydrateStateUsing(function (Get $get, ?Model $record, string $state) {
                                if($record) {
                                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $state, app()->getCurrentScheduledConference()->getMeta('timezone'))->setDateFrom($record->timeline->date);
                                    return $date->copy()->setTimezone('UTC');
                                } else {
                                    $timeline = Timeline::where('id', $get('timeline_id'))->first();
                                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $state, app()->getCurrentScheduledConference()->getMeta('timezone'))->setDateFrom($timeline->date);
                                    return $date->copy()->setTimezone('UTC');
                                }
                            })
                            ->required()
                            ->before('end_at'),
                        TimePicker::make('end_at')
                            ->label(__("general.time_end"))
                            ->seconds(false)
                            ->native(false)
                            ->dehydrateStateUsing(function (Get $get, ?Model $record, string $state) {
                                if($record) {
                                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $state, app()->getCurrentScheduledConference()->getMeta('timezone'))->setDateFrom($record->timeline->date);
                                    return $date->copy()->setTimezone('UTC');
                                } else {
                                    $timeline = Timeline::where('id', $get('timeline_id'))->first();
                                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $state, app()->getCurrentScheduledConference()->getMeta('timezone'))->setDateFrom($timeline->date);
                                    return $date->copy()->setTimezone('UTC');
                                }
                            })
                            ->required()
                            ->after('start_at'),
                    ]),
                Checkbox::make('require_attendance')
                    ->disabled(fn(?Model $record) => (bool) $record ? $record->timeline->isRequireAttendance() : false)
                    ->helperText(fn(?Model $record) => $record ? ($record->timeline->isRequireAttendance() ? __('general.timeline_are_requiring_attendance_this_is_disabled') : null) : null),
                Select::make('timeline_id')
                    ->label(__('general.belong_to_timeline'))
                    ->options(Timeline::get()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                static::$model::query()
                    ->with(['timeline' => function (EloquentBuilder $query) {
                        $query->orderBy('date', 'ASC');
                    }])
            )
            ->columns([
                TextColumn::make('time_span')
                    ->label(__('general.time'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('start_at', $direction)
                            ->orderBy('end_at', $direction);
                    }),
                TextColumn::make('name')
                    ->label(__('general.session_name'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('sessions.name', 'like', "%{$search}%");
                    })
                    ->sortable(),
                IconColumn::make('require_attendance')
                    ->icon(fn(Model $record) => match ($record->getRequiresAttendanceStatus()) {
                        'timeline' => 'heroicon-o-stop-circle',
                        'not-required' => 'heroicon-o-x-circle',
                        'required' => 'heroicon-o-check-circle',
                    })
                    ->color(fn(Model $record) => match ($record->getRequiresAttendanceStatus()) {
                        'timeline' => Color::Blue,
                        'not-required' => Color::Red,
                        'required' => Color::Green,
                    })
                    ->tooltip(
                        fn(Model $record) => $record->getRequiresAttendanceStatus() === 'timeline' ?
                            __('general.attendance_arent_required') : null
                    )
                    ->alignCenter(),
            ])
            ->defaultSort('time_span')
            ->actions([
                EditAction::make()
                    ->modalHeading(__('general.edit_session'))
                    ->model(static::$model)
                    ->form(fn(Form $form) => $this->form($form))
                    ->authorize(fn(Model $record) => auth()->user()->can('update', $record)),
                ActionGroup::make([
                    DeleteAction::make()
                        ->authorize(fn(Model $record) => auth()->user()->can('delete', $record)),
                ])
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('Timeline:delete'),
            ])
            ->groups([
                Group::make('timeline.name')
                    ->label('')
                    ->getDescriptionFromRecordUsing(fn(Model $record): string => $record->timeline->date->format(Setting::get('format_date')))
                    ->orderQueryUsing(function (Builder $query, string $direction) {
                        return $query
                            ->select(['sessions.*', 'timelines.date'])
                            ->leftJoin('timelines', 'timelines.id', '=', 'sessions.timeline_id')
                            ->orderBy('timelines.date', $direction);
                    }),
            ])
            ->groupingSettingsHidden()
            ->defaultGroup('timeline.name')
            ->paginated(false);
    }
}
