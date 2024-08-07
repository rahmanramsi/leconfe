<?php

namespace App\Panel\ScheduledConference\Resources\TimelineResource\Pages;

use Filament\Actions;
use App\Models\Agenda;
use App\Facades\Setting;
use App\Models\Timeline;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use App\Forms\Components\TinyEditor;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Toggle;
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
use Illuminate\Contracts\Support\Htmlable;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Panel\ScheduledConference\Resources\TimelineResource;

class ListAgenda extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $model = Agenda::class;

    protected static string $resource = TimelineResource::class;

    protected static string $view = 'panel.scheduledConference.resources.timeline-resource.pages.list-agenda';

    public ?Timeline $timeline = null;

    public function mount(?Timeline $record): void
    {
        $this->timeline = $record;
    }

    public function getTitle(): string | Htmlable
    {
        $timeline = $this->timeline->name;
        return "Timeline: $timeline";
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
                ->label('Add agenda')
                ->modalHeading('Add Agenda')
                ->model(static::$model)
                ->form(fn (Form $form) => $this->form($form))
                ->mutateFormDataUsing(function (?array $data) {
                    $data['timeline_id'] = $this->timeline->id;
                    $data['date'] = $this->timeline->date;
                    return $data;
                })
                ->authorize('Timeline:create'),
        ];
    }

    public static function getAgendaForm(): array
    {
        return [
            TextInput::make('name')
                ->label('Agenda name')
                ->required(),
            TinyEditor::make('public_details')
                ->minHeight(200)
                ->hint('Detail that visible to all user'),
            TinyEditor::make('details')
                ->minHeight(200)
                ->hint('Detail that visible only to participant'),
            Grid::make(2)
                ->schema([
                    TimePicker::make('time_start')
                        ->required()
                        ->before('time_end'),
                    TimePicker::make('time_end')
                        ->required()
                        ->after('time_start'),
                ]),
            Checkbox::make('requires_attendance')
                ->disabled(fn (?Model $record) => (boolean) $record ? $record->timeline->isRequiresAttendance() : false)
                ->helperText(fn (?Model $record) => $record ? ($record->timeline->isRequiresAttendance() ? 'Timeline are requiring attendance, this is disabled.' : null) : null),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...static::getAgendaForm()
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                static::$model::query()
                    ->where('timeline_id', $this->timeline->id)
            )
            ->columns([
                TextColumn::make('time_span')
                    ->label('Time')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('time_start', $direction)
                            ->orderBy('time_end', $direction);
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
            ->paginated(false);
    }
}
