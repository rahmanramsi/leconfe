<?php

namespace App\Panel\ScheduledConference\Resources\TimelineResource\Pages;

use App\Facades\Setting;
use App\Forms\Components\TinyEditor;
use App\Models\Session;
use App\Models\Timeline;
use App\Panel\ScheduledConference\Resources\TimelineResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListSession extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $model = Session::class;

    protected static string $resource = TimelineResource::class;

    protected static string $view = 'panel.scheduledConference.resources.timeline-resource.pages.list-session';

    public ?Timeline $timeline = null;

    public function mount(?Timeline $record): void
    {
        $this->timeline = $record;
    }

    public function getTitle(): string|Htmlable
    {
        return $this->timeline->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        $timeline = $this->timeline;
        $date = $timeline->date->format(Setting::get('format_date'));
        $timeSpan = $timeline->time_span;

        return "$date ($timeSpan)";
    }

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
                ->mutateFormDataUsing(function (array $data) {
                    $data['timeline_id'] = $this->timeline->id;

                    return $data;
                })
                ->form(fn (Form $form) => $this->form($form))
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
                            ->label(__('general.time_start'))
                            ->seconds(false)
                            ->native(false)
                            ->formatStateUsing(fn (?Model $record) => $record ? $record->getStartDate() : null)
                            ->dehydrateStateUsing(function (?Model $record, string $state) {
                                if ($record) {
                                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $state, app()->getCurrentScheduledConference()->getMeta('timezone'))->setDateFrom($record->timeline->date);

                                    return $date->copy()->setTimezone('UTC');
                                } else {
                                    $timeline = $this->timeline;
                                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $state, app()->getCurrentScheduledConference()->getMeta('timezone'))->setDateFrom($timeline->date);

                                    return $date->copy()->setTimezone('UTC');
                                }
                            })
                            ->required()
                            ->before('end_at'),
                        TimePicker::make('end_at')
                            ->label(__('general.time_end'))
                            ->seconds(false)
                            ->native(false)
                            ->formatStateUsing(fn (?Model $record) => $record ? $record->getEndDate() : null)
                            ->dehydrateStateUsing(function (?Model $record, string $state) {
                                if ($record) {
                                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $state, app()->getCurrentScheduledConference()->getMeta('timezone'))->setDateFrom($record->timeline->date);

                                    return $date->copy()->setTimezone('UTC');
                                } else {
                                    $timeline = $this->timeline;
                                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $state, app()->getCurrentScheduledConference()->getMeta('timezone'))->setDateFrom($timeline->date);

                                    return $date->copy()->setTimezone('UTC');
                                }
                            })
                            ->required()
                            ->after('start_at'),
                    ]),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                static::$model::query()
                    ->where('timeline_id', $this->timeline->id)
                    ->with('timeline')
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
                    }),
                ToggleColumn::make('require_attendance')
                    ->disabled(fn (Model $record) => $record->timeline->isRequireAttendance())
                    ->tooltip(
                        fn (Model $record) => $record->getRequiresAttendanceStatus() === 'timeline' ?
                            __('general.attendance_arent_required') : null
                    )
                    ->alignCenter(),
            ])
            ->defaultSort('time_span')
            ->emptyStateIcon('heroicon-m-calendar-days')
            ->emptyStateHeading('Empty!')
            ->emptyStateDescription('Create new session to get started.')
            ->actions([
                EditAction::make()
                    ->modalHeading(__('general.edit_session'))
                    ->model(static::$model)
                    ->form(fn (Form $form) => $this->form($form))
                    ->authorize(fn (Model $record) => auth()->user()->can('update', $record)),
                ActionGroup::make([
                    DeleteAction::make()
                        ->authorize(fn (Model $record) => auth()->user()->can('delete', $record)),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('Timeline:delete'),
            ])
            ->paginated(false);
    }
}
