<?php

namespace App\Panel\ScheduledConference\Livewire;

use App\Models\Topic;
use Livewire\Component;
use App\Facades\Setting;
use App\Models\Timeline;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Registration;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use App\Actions\Topics\TopicCreateAction;
use App\Actions\Topics\TopicUpdateAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class RegistrantAttendance extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public Registration $registration;

    public Timeline $timeline;
    
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
            ->heading($this->timeline->name)
            ->description(function () {
                $date = $this->timeline->date->format(Setting::get('format_date'));
                $timeSpan = $this->timeline->time_span;

                return "{$timeSpan} ({$date})";
            })
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
                    ->icon(fn(Model $record) => match ($record->getRequiresAttendanceStatus()) {
                        'required' => 'heroicon-o-check',
                        default => 'heroicon-o-x-mark',
                    })
                    ->color(fn(Model $record) => match ($record->getRequiresAttendanceStatus()) {
                        'required' => Color::Green,
                        'not-required' => Color::Gray,
                        'timeline' => Color::Blue,
                    })
                    ->tooltip(
                        fn(Model $record) => match ($record->getRequiresAttendanceStatus()) {
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
                    ->trueIcon('heroicon-o-check')
                    ->trueColor(Color::Green)
                    ->falseIcon('heroicon-o-x-mark')
                    ->falseColor(Color::Red)
                    ->tooltip(function (Model $record) {
                        if ($record->timeline->isRequireAttendance()) {
                            return __('general.attendance_per_day_required');
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
                    ->label(__('general.attendance_time'))
                    ->placeholder('-')
                    ->dateTime(Setting::get('format_time')),
            ])
            ->defaultSort('time_span');
    }

    // public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             TextInput::make('name')
    //                 ->label(__('general.name'))
    //                 ->required(),
    //         ]);
    // }

    public function render()
    {
        return view('tables.table');
    }
}
