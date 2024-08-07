<?php

namespace App\Panel\ScheduledConference\Resources\TimelineResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Agenda;
use App\Facades\Setting;
use App\Models\Timeline;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Grouping\Group;
use App\Forms\Components\TinyEditor;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
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

class ListAllAgenda extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $model = Agenda::class;

    protected static string $resource = TimelineResource::class;

    protected static string $view = 'panel.scheduledConference.resources.timeline-resource.pages.list-agenda';

    protected static ?string $title = 'Agenda list';

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
                ->label('Add agenda')
                ->modalHeading('Add Agenda')
                ->model(static::$model)
                ->form(fn (Form $form) => $this->form($form))
                ->mutateFormDataUsing(function (array $data) {
                    $timeline = Timeline::where('id', $data['timeline_id'])->first();
                    if($timeline) {
                        $data['date'] = $timeline->date;
                    }
                    return $data;
                })
                ->authorize('Timeline:create'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...ListAgenda::getAgendaForm(),
                Select::make('timeline_id')
                    ->label('Belong to timeline')
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
            )
            ->columns([
                TextColumn::make('time_span')
                    ->label('Time')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('time_start', $direction);
                    }),
                TextColumn::make('name')
                    ->label('Agenda name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('agendas.name', 'like', "%{$search}%");
                    })
                    ->sortable(),
                TextColumn::make('public_details')
                    ->label('Public Details')
                    ->placeholder('Empty')
                    ->formatStateUsing(fn () => 'Not Empty'),
                TextColumn::make('details')
                    ->label('Participant Details')
                    ->placeholder('Empty')
                    ->formatStateUsing(fn () => 'Not Empty'),
                IconColumn::make('requires_attendance')
                    ->icon(fn (Model $record) => match($record->getRequiresAttendanceStatus()) {
                        'timeline' => 'heroicon-o-stop-circle',
                        'not-required' => 'heroicon-o-x-circle',
                        'required' => 'heroicon-o-check-circle',
                    })
                    ->color(fn (Model $record) => match($record->getRequiresAttendanceStatus()) {
                        'timeline' => Color::Blue,
                        'not-required' => Color::Red,
                        'required' => Color::Green,
                    })
                    ->tooltip(fn (Model $record) => $record->getRequiresAttendanceStatus() === 'timeline' ?
                        "Attendance are'nt required because the timeline had it active." : null
                    )
                    ->alignCenter(),
            ])
            ->defaultSort('time_span')
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Agenda')
                    ->model(static::$model)
                    ->form(fn (Form $form) => $this->form($form))
                    ->authorize('Timeline:edit'),
                ActionGroup::make([
                    DeleteAction::make()
                    ->authorize('Timeline:delete'),
                ])
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('Timeline:delete'),
            ])
            ->groups([
                Group::make('timeline.name')
                    ->label('Timeline')
                    ->getDescriptionFromRecordUsing(fn (Model $record): string => Carbon::parse($record->timeline->date)->format(Setting::get('format_date')))
                    ->orderQueryUsing(function (Builder $query, string $direction) {
                        return $query
                            ->select(['agendas.*', 'timelines.date'])
                            ->leftJoin('timelines', 'timelines.id', '=', 'agendas.timeline_id')
                            ->orderBy('timelines.date', $direction);
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('timeline.name')
            ->paginated(true);
    }
}
