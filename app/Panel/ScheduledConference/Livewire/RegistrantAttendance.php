<?php

namespace App\Panel\ScheduledConference\Livewire;

use App\Facades\Setting;
use App\Models\Registration;
use App\Models\RegistrationAttendance;
use App\Models\Timeline;
use App\Panel\ScheduledConference\Resources\TimelineResource;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Component;

class RegistrantAttendance extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions, InteractsWithForms, InteractsWithTable;

    public Registration $registration;

    public Timeline $timeline;

    public const DAY_ATTENDANCE_MARK_TYPE_IN = 0;

    public const DAY_ATTENDANCE_MARK_TYPE_OUT = 1;

    public function mount(Registration $registration, Timeline $timeline): void
    {
        $this->registration = $registration;
        $this->timeline = $timeline;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->timeline
                ->sessions()
                ->with(['timeline'])
                ->getQuery()
            )
            ->heading(function () {
                $baseHeading = "{$this->timeline->name}";
                if (($attendance = $this->registration->getAttendance($this->timeline)) && $this->timeline->isRequireAttendance()) {
                    $attendanceTime = $attendance->created_at->format(Setting::get('format_time'));

                    return new HtmlString(BladeCompiler::render(<<<BLADE
                        {$baseHeading}
                        <span class="mx-2 inline-block">
                            <x-filament::badge color="info" class="!w-fit">
                                Attended {$attendanceTime}
                            </x-filament::badge>
                        </span>
                    BLADE));
                }

                return $baseHeading;
            })
            ->description(function () {
                $date = $this->timeline->date->format(Setting::get('format_date'));
                $timeSpan = $this->timeline->time_span;

                return "{$date} ({$timeSpan})";
            })
            ->headerActions([
                Action::make('mark_in_day')
                    ->label('Mark in')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Green)
                    ->link()
                    ->modalWidth('2xl')
                    ->successNotificationTitle(__('general.saved'))
                    ->failureNotificationTitle(__('general.data_could_not_saved'))
                    ->form(function (Form $form) {
                        return $form
                            ->schema([
                                Fieldset::make('Attendance date')
                                    ->label(__('general.attendance_date'))
                                    ->schema([
                                        Placeholder::make('attendance_date')
                                            ->label('')
                                            ->content(fn () => $this->timeline->date->format(Setting::get('format_date'))),
                                    ]),
                                TimePicker::make('attendance_time')
                                    ->default(now())
                                    ->helperText(__('general.input_participant_attendance_time'))
                                    ->seconds(false)
                                    ->native(false)
                                    ->hint(fn () => $this->timeline->time_span)
                                    ->required(),
                            ])
                            ->columns(1);
                    })
                    ->action(function (array $data, Action $action) {
                        try {
                            $timeline = $this->timeline;
                            $time = (string) $timeline->date->setTimeFromTimeString($data['attendance_time']);

                            $registrationAttendance = RegistrationAttendance::create([
                                'timeline_id' => $timeline->id,
                                'registration_id' => $this->registration->id,
                                'created_at' => $time,
                                'updated_at' => $time,
                            ]);

                            $registrationAttendance->created_at = $time;
                            $registrationAttendance->updated_at = $time;
                            $registrationAttendance->save();
                        } catch (\Throwable $th) {
                            $action->failure();
                            throw $th;
                        }

                        $action->success();
                    })
                    ->visible(fn () => ! $this->registration->isAttended($this->timeline) && $this->timeline->isRequireAttendance())
                    ->authorize('markIn', RegistrationAttendance::class),
                Action::make('mark_out_day')
                    ->label('Mark out')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Red)
                    ->link()
                    ->requiresConfirmation()
                    ->modalWidth('2xl')
                    ->successNotificationTitle(__('general.saved'))
                    ->failureNotificationTitle(__('general.data_could_not_saved'))
                    ->action(function (?array $data, Action $action) {
                        try {
                            $attendance = $this->registration->getAttendance($this->timeline);

                            if (! $attendance) {
                                return $action->failure();
                            }

                            $attendance->delete();
                        } catch (\Throwable $th) {
                            $action->failure();
                            throw $th;
                        }

                        $action->success();
                    })
                    ->visible(fn () => $this->registration->isAttended($this->timeline) && $this->timeline->isRequireAttendance())
                    ->authorize('markOut', RegistrationAttendance::class),
            ])
            ->columns([
                TextColumn::make('time_span')
                    ->label(__('general.time'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('start_at', $direction)
                            ->orderBy('end_at', $direction);
                    }),
                TextColumn::make('name')
                    ->label(__('general.session_name')),
                IconColumn::make('require_attendance')
                    ->icon(fn (Model $record) => match ($record->getRequiresAttendanceStatus()) {
                        'required' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-x-circle',
                    })
                    ->color(fn (Model $record) => match ($record->getRequiresAttendanceStatus()) {
                        'required' => Color::Green,
                        'not-required' => Color::Gray,
                        'timeline' => Color::Blue,
                    })
                    ->tooltip(
                        fn (Model $record) => match ($record->getRequiresAttendanceStatus()) {
                            'not-required' => __('general.attendance_required'),
                            'timeline' => __('general.attendance_required_per_day_attendance'),
                            default => null,
                        }
                    )
                    ->alignCenter(),
                IconColumn::make('attended')
                    ->label(__('general.attendance_status'))
                    ->getStateUsing(function (Model $record) {
                        if ($this->registration->isAttended($record->timeline)) {
                            return true;
                        }

                        if ($this->registration->isAttended($record)) {
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
                        if ($userTimelineAttendance = $this->registration->getAttendance($record->timeline)) {
                            return $userTimelineAttendance->created_at;
                        }

                        if ($userSessionAttendance = $this->registration->getAttendance($record)) {
                            return $userSessionAttendance->created_at;
                        }

                        return null;
                    })
                    ->label(__('general.attendance_time'))
                    ->placeholder('-')
                    ->dateTime(Setting::get('format_time')),
            ])
            ->actions([
                Action::make('mark_in')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Green)
                    ->modalWidth('xl')
                    ->successNotificationTitle(__('general.saved'))
                    ->failureNotificationTitle(__('general.data_could_not_saved'))
                    ->form(function (Form $form, Model $record) {
                        return $form
                            ->schema([
                                Fieldset::make('Attendance date')
                                    ->label(__('general.attendance_date'))
                                    ->schema([
                                        Placeholder::make('attendance_date')
                                            ->label('')
                                            ->content(fn () => $record->timeline->date->format(Setting::get('format_date'))),
                                    ]),
                                TimePicker::make('attendance_time')
                                    ->default(now())
                                    ->helperText(__('general.input_participant_attendance_time'))
                                    ->seconds(false)
                                    ->native(false)
                                    ->hint(fn () => $record->time_span)
                                    ->required(),
                            ])
                            ->columns(1);
                    })
                    ->action(function (Model $record, array $data, Action $action) {
                        try {
                            $time = (string) $record->timeline->date->setTimeFromTimeString($data['attendance_time']);

                            $registrationAttendance = RegistrationAttendance::create([
                                'session_id' => $record->id,
                                'registration_id' => $this->registration->id,
                            ]);

                            $registrationAttendance->created_at = $time;
                            $registrationAttendance->updated_at = $time;
                            $registrationAttendance->save();
                        } catch (\Throwable $th) {
                            $action->failure();
                            throw $th;
                        }

                        $action->success();
                    })
                    ->visible(fn (Model $record) => ! $this->registration->isAttended($record) && $record->isRequireAttendance())
                    ->authorize('markIn', RegistrationAttendance::class),
                Action::make('mark_out')
                    ->icon('heroicon-m-finger-print')
                    ->color(Color::Red)
                    ->requiresConfirmation()
                    ->successNotificationTitle(__('general.saved'))
                    ->failureNotificationTitle(__('general.data_could_not_saved'))
                    ->action(function (Model $record, Action $action) {
                        $attendance = $this->registration->getAttendance($record);

                        if (! $attendance) {
                            return $action->failure();
                        }

                        $attendance->delete();

                        $action->success();
                    })
                    ->visible(fn (Model $record) => $this->registration->isAttended($record) && $record->isRequireAttendance())
                    ->authorize('markOut', RegistrationAttendance::class),
            ])
            ->emptyStateIcon('heroicon-m-calendar-days')
            ->emptyStateHeading('Empty!')
            ->emptyStateDescription('Create new session to get started.')
            ->emptyStateActions([
                Action::make('timelines')
                    ->label(__('general.timelines'))
                    ->color('gray')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition(IconPosition::After)
                    ->url(fn () => TimelineResource::getUrl('session', ['record' => $this->timeline]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('time_span')
            ->paginated(false);
    }

    public function render()
    {
        return view('tables.table');
    }
}
