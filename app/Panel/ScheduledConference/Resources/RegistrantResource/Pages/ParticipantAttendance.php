<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use Carbon\Carbon;
use App\Models\Agenda;
use App\Facades\Setting;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Registration;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use App\Models\RegistrationAttendance;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Panel\ScheduledConference\Resources\RegistrantResource;

class ParticipantAttendance extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $model = Agenda::class;
    
    protected static string $resource = RegistrantResource::class;

    protected static string $view = 'panel.scheduledConference.resources.registrant-resource.pages.participant-attendance';
    
    public Registration $registration;

    public function mount(?Registration $record): void
    {
        $this->registration = $record;
    }

    public function getTitle(): string | Htmlable
    {
        return $this->registration->user->full_name;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
            'Attendance',
            $this->getTitle(),
        ];

        return $breadcrumbs;
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
                IconColumn::make('attended')
                    ->getStateUsing(function (Model $record) {
                        if($this->registration->isAttended($record->timeline)) {
                            return true;
                        }

                        if($this->registration->isAttended($record)) {
                            return true;
                        }

                        return false;
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->trueColor(Color::Green)
                    ->falseIcon('heroicon-o-x-circle')
                    ->falseColor(Color::Red)
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('attend_at')
                    ->getStateUsing(function (Model $record) { 
                        if($userTimelineAttendance = $this->registration->getAttendance($record->timeline)) {
                            return $userTimelineAttendance->created_at;
                        }

                        if($userAgendaAttendance = $this->registration->getAttendance($record)) {
                            return $userAgendaAttendance->created_at;
                        }

                        return null;
                    })
                    ->label('Attendance Time')
                    ->placeholder('Not Attended')
                    ->dateTime(Setting::get('format_time')),
            ])
            ->defaultSort('time_span')
            ->groups([
                Group::make('timeline.name')
                    ->label('Timeline')
                    ->getDescriptionFromRecordUsing(function (Model $record): string { 
                        $date = Carbon::parse($record->timeline->date)->format(Setting::get('format_date'));
                        $isRequiresAttendance = $record->timeline->isRequiresAttendance() ? "(Per-day attendance are required)" : null;

                        return "$date $isRequiresAttendance";
                    })
                    ->orderQueryUsing(function (Builder $query, string $direction) {
                        return $query
                            ->select(['agendas.*', 'timelines.date'])
                            ->leftJoin('timelines', 'timelines.id', '=', 'agendas.timeline_id')
                            ->orderBy('timelines.date', $direction);
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('timeline.name')
            ->actions([
                // ---[ Timeline ]---
                Action::make('mark_in_day')
                    ->label('Mark in (Day)')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Green)
                    ->modalWidth('xl')
                    ->form(function (Form $form, Model $record) {
                        return $form
                            ->schema([
                                Fieldset::make('Attendance date')
                                    ->schema([
                                        Placeholder::make('attendance_date')
                                            ->label('')
                                            ->content(fn () => Carbon::parse($record->date)->format(Setting::get('format_date'))),
                                    ]),
                                TimePicker::make('attendance_time')
                                    ->helperText('Input participant attendance time.')
                                    ->hint(fn () => $record->timeline->time_span)
                                    ->required(),
                            ])
                            ->columns(1);
                    })
                    ->action(function (Model $record, array $data, Action $action) {
                        try {
                            $time = (string) Carbon::parse($record->date)->setTimeFromTimeString($data['attendance_time']);
                            
                            RegistrationAttendance::create([
                                'timeline_id' => $record->timeline->id,
                                'registration_id' => $this->registration->id,
                                'created_at' => $time,
                                'updated_at' => $time,
                            ]);
                        } catch (\Throwable $th) {
                            throw $th;
                            
                            $action->sendFailureNotification();
                        }
                    })
                    ->visible(fn (Model $record) => !$this->registration->isAttended($record->timeline) && $record->timeline->isRequiresAttendance()),
                Action::make('mark_out_day')
                    ->label('Mark out (Day)')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Red)
                    ->requiresConfirmation()
                    ->action(function (Model $record) {
                        $attendance = $this->registration->getAttendance($record->timeline);

                        if(!$attendance) return;

                        $attendance->delete();
                    })
                    ->visible(fn (Model $record) => $this->registration->isAttended($record->timeline) && $record->timeline->isRequiresAttendance()),
                // ---[ Agenda ]---
                Action::make('mark_in')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Green)
                    ->modalWidth('xl')
                    ->form(function (Form $form, Model $record) {
                        return $form
                            ->schema([
                                Fieldset::make('Attendance date')
                                    ->schema([
                                        Placeholder::make('attendance_date')
                                            ->label('')
                                            ->content(fn () => Carbon::parse($record->date)->format(Setting::get('format_date'))),
                                    ]),
                                TimePicker::make('attendance_time')
                                    ->helperText('Input participant attendance time.')
                                    ->hint(fn () => $record->time_span)
                                    ->required(),
                            ])
                            ->columns(1);
                    })
                    ->action(function (Model $record, array $data, Action $action) {
                        try {
                            $time = (string) Carbon::parse($record->date)->setTimeFromTimeString($data['attendance_time']);
                            
                            RegistrationAttendance::create([
                                'agenda_id' => $record->id,
                                'registration_id' => $this->registration->id,
                                'created_at' => $time,
                                'updated_at' => $time,
                            ]);
                        } catch (\Throwable $th) {
                            throw $th;
                            
                            $action->sendFailureNotification();
                        }
                    })
                    ->visible(fn (Model $record) => !$this->registration->isAttended($record) && $record->isRequiresAttendance()),
                Action::make('mark_out')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Red)
                    ->requiresConfirmation()
                    ->action(function (Model $record) {
                        $attendance = $this->registration->getAttendance($record);

                        if(!$attendance) return;

                        $attendance->delete();
                    })
                    ->visible(fn (Model $record) => $this->registration->isAttended($record) && $record->isRequiresAttendance()),
            ])
            ->paginated(false);
    }
}
