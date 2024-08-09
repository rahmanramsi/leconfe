<?php

namespace App\Panel\ScheduledConference\Resources\TimelineResource\Pages;

use Filament\Actions;
use App\Models\Session;
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

class ListSession extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $model = Session::class;

    protected static string $resource = TimelineResource::class;

    protected static string $view = 'panel.scheduledConference.resources.timeline-resource.pages.list-session';

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
                ->label('Add session')
                ->modalHeading('Add Session')
                ->model(static::$model)
                ->form(fn (Form $form) => $this->form($form))
                ->mutateFormDataUsing(function (?array $data) {
                    $data['timeline_id'] = $this->timeline->id;
                    $data['date'] = $this->timeline->date;
                    return $data;
                })
                ->authorize('create', Session::class),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...self::$resource::getSessionForm()
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
                    ->label('Session name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('sessions.name', 'like', "%{$search}%");
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
                    ->modalHeading('Edit Session')
                    ->model(static::$model)
                    ->form(fn (Form $form) => $this->form($form))
                    ->authorize(fn (Model $record) => auth()->user()->can('update', $record)),
                ActionGroup::make([
                    DeleteAction::make()
                        ->authorize(fn (Model $record) => auth()->user()->can('delete', $record)),
                ])
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('Timeline:delete'),
            ])
            ->paginated(false);
    }
}
