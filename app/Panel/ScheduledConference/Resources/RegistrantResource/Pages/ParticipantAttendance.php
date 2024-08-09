<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Session;
use Filament\Forms\Get;
use App\Facades\Setting;
use App\Models\Timeline;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Registration;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Panel\ScheduledConference\Resources\RegistrantResource;

class ParticipantAttendance extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $model = Session::class;

    protected static string $resource = RegistrantResource::class;

    protected static string $view = 'panel.scheduledConference.resources.registrant-resource.pages.participant-attendance';

    public Registration $registration;

    public const DAY_ATTENDANCE_MARK_TYPE_IN = 0;
    public const DAY_ATTENDANCE_MARK_TYPE_OUT = 1;

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

    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_in_day')
                ->label('Mark in (Day)')
                ->icon('heroicon-m-finger-print')
                ->color(Color::Green)
                ->modalWidth('2xl')
                ->successNotificationTitle('Saved!')
                ->failureNotificationTitle('Data could not be saved.')
                ->form(function (Form $form) {
                    return $form
                        ->schema([
                            Select::make('timeline_id')
                                ->label('Timeline')
                                ->options(static::getTimelineListOption(self::DAY_ATTENDANCE_MARK_TYPE_IN, $this->registration))
                                ->searchable()
                                ->required()
                                ->live(),
                            Fieldset::make('Attendance date')
                                ->schema([
                                    Placeholder::make('attendance_date')
                                        ->label('')
                                        ->content(function (Get $get) {
                                            if (!$get('timeline_id')) {
                                                return 'Select the timeline first.';
                                            }

                                            $timeline = Timeline::where('id', $get('timeline_id'))->first();
                                            if (!$timeline) {
                                                return 'Select valid timeline.';
                                            }

                                            return Carbon::parse($timeline->date)->format(Setting::get('format_date'));
                                        }),
                                ]),
                            TimePicker::make('attendance_time')
                                ->helperText('Input participant attendance time.')
                                ->hint(function (Get $get) {
                                    if (!$get('timeline_id')) {
                                        return null;
                                    }

                                    $timeline = Timeline::where('id', $get('timeline_id'))->first();
                                    if (!$timeline) {
                                        return null;
                                    }

                                    return $timeline->time_span;
                                })
                                ->required(),
                        ])
                        ->columns(1);
                })
                ->action(function (array $data, Actions\Action $action) {
                    try {
                        $timeline = Timeline::where('id', $data['timeline_id'])->first();
                        $time = (string) Carbon::parse($timeline->date)->setTimeFromTimeString($data['attendance_time']);

                        RegistrationAttendance::create([
                            'timeline_id' => $timeline->id,
                            'registration_id' => $this->registration->id,
                            'created_at' => $time,
                            'updated_at' => $time,
                        ]);

                        $action->sendSuccessNotification();
                    } catch (\Throwable $th) {
                        $action->sendFailureNotification();
                    }
                })
                ->authorize('markIn', RegistrationAttendance::class),
            Actions\Action::make('mark_out_day')
                ->label('Mark out (Day)')
                ->icon('heroicon-m-finger-print')
                ->color(Color::Red)
                ->requiresConfirmation()
                ->modalWidth('2xl')
                ->successNotificationTitle('Saved!')
                ->failureNotificationTitle('Data could not be saved.')
                ->form(function (Form $form) {
                    return $form
                        ->schema([
                            Select::make('timeline_id')
                                ->label('Timeline')
                                ->options(static::getTimelineListOption(self::DAY_ATTENDANCE_MARK_TYPE_OUT, $this->registration))
                                ->searchable()
                                ->required(),
                        ])
                        ->columns(1);
                })
                ->action(function (?array $data, Actions\Action $action) {
                    try {
                        $timeline = Timeline::where('id', $data['timeline_id'])->first();
                        $attendance = $this->registration->getAttendance($timeline);

                        if (!$attendance) $action->sendFailureNotification();

                        $attendance->delete();

                        $action->sendSuccessNotification();
                    } catch (\Throwable $th) {
                        $action->sendFailureNotification();
                    }
                })
                ->authorize('markOut', RegistrationAttendance::class),
        ];
    }

    public static function getTimelineListOption(int $type, Registration $registration): array
    {
        $timelinesOption = [];
        $timelines = Timeline::where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->orderBy('date', 'ASC')
            ->get();

        foreach ($timelines as $timeline) {
            if ($type === self::DAY_ATTENDANCE_MARK_TYPE_IN && $registration->isAttended($timeline)) {
                continue;
            }

            if ($type === self::DAY_ATTENDANCE_MARK_TYPE_IN && !$timeline->canShown()) {
                continue;
            }

            if ($type === self::DAY_ATTENDANCE_MARK_TYPE_OUT && !$registration->isAttended($timeline)) {
                continue;
            }

            $timelineName = $timeline->name;
            $timelineDate = Carbon::parse($timeline->date)->format(Setting::get('format_date'));
            $timelineTimeSpan = $timeline->time_span;

            $timelinesOption[$timeline->id] = "($timelineDate, $timelineTimeSpan) $timelineName ";
        }
        return $timelinesOption;
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
                    ->label('Session name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('sessions.name', 'like', "%{$search}%");
                    })
                    ->sortable(),
                IconColumn::make('requires_attendance')
                    ->icon(fn (Model $record) => match ($record->getRequiresAttendanceStatus()) {
                        'required' => 'heroicon-o-check',
                        default => 'heroicon-o-x-mark',
                    })
                    ->color(fn (Model $record) => match ($record->getRequiresAttendanceStatus()) {
                        'required' => Color::Green,
                        'not-required' => Color::Gray,
                        'timeline' => Color::Blue,
                    })
                    ->tooltip(
                        fn (Model $record) => match($record->getRequiresAttendanceStatus()) {
                            'not-required' => "Attendance are'nt required.",
                            'timeline' => "Attendance are'nt required because per day attendance are required.",
                            default => null,
                        }
                    )
                    ->alignCenter(),
                IconColumn::make('attended')
                    ->label('Attendance Status')
                    ->getStateUsing(function (Model $record) {
                        if ($this->registration->isAttended($record->timeline)) {
                            return true;
                        }

                        if ($this->registration->isAttended($record)) {
                            return true;
                        }

                        return false;
                    })
                    ->trueIcon('heroicon-o-check')
                    ->trueColor(Color::Green)
                    ->falseIcon('heroicon-o-x-mark')
                    ->falseColor(Color::Red)
                    ->tooltip(function (Model $record) {
                        if ($record->timeline->isRequiresAttendance()) {
                            return 'Per day attendance are required, mark in and out on top of the page.';
                        }
                    })
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('attend_at')
                    ->getStateUsing(function (Model $record) {
                        if ($userTimelineAttendance = $this->registration->getAttendance($record->timeline)) {
                            return $userTimelineAttendance->created_at;
                        }

                        if ($userSessionAttendance = $this->registration->getAttendance($record)) {
                            return $userSessionAttendance->created_at;
                        }

                        return null;
                    })
                    ->label('Attendance Time')
                    ->placeholder('-')
                    ->dateTime(Setting::get('format_time')),
            ])
            ->defaultSort('time_span')
            ->groups([
                Group::make('timeline.name')
                    ->label('')
                    ->getDescriptionFromRecordUsing(function (Model $record): string {
                        $date = Carbon::parse($record->timeline->date)->format(Setting::get('format_date'));
                        $isRequiresAttendance = $record->timeline->isRequiresAttendance() ? "(Per day attendance are required)" : null;

                        return "$date $isRequiresAttendance";
                    })
                    ->orderQueryUsing(function (Builder $query, string $direction) {
                        return $query
                            ->select(['sessions.*', 'timelines.date'])
                            ->leftJoin('timelines', 'timelines.id', '=', 'sessions.timeline_id')
                            ->orderBy('timelines.date', $direction);
                    })
            ])
            ->defaultGroup('timeline.name')
            ->groupingSettingsHidden()
            ->actions([
                Action::make('mark_in')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Green)
                    ->modalWidth('xl')
                    ->successNotificationTitle('Saved!')
                    ->failureNotificationTitle('Data could not be saved.')
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
                                'session_id' => $record->id,
                                'registration_id' => $this->registration->id,
                                'created_at' => $time,
                                'updated_at' => $time,
                            ]);

                            $action->sendSuccessNotification();
                        } catch (\Throwable $th) {
                            throw $th;

                            $action->sendFailureNotification();
                        }
                    })
                    ->visible(fn (Model $record) => !$this->registration->isAttended($record) && $record->isRequiresAttendance())
                    ->authorize('markIn', RegistrationAttendance::class),
                Action::make('mark_out')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Red)
                    ->requiresConfirmation()
                    ->successNotificationTitle('Saved!')
                    ->failureNotificationTitle('Data could not be saved.')
                    ->action(function (Model $record, Action $action) {
                        $attendance = $this->registration->getAttendance($record);

                        if (!$attendance) return;

                        $attendance->delete();

                        $action->sendSuccessNotification();
                    })
                    ->visible(fn (Model $record) => $this->registration->isAttended($record) && $record->isRequiresAttendance())
                    ->authorize('markOut', RegistrationAttendance::class),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('Timeline:delete'),
            ])
            ->paginated(false);
    }
}
