<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use App\Infolists\Components\LivewireEntry;
use App\Infolists\Components\VerticalTabs\Tab;
use App\Infolists\Components\VerticalTabs\Tabs;
use App\Models\Registration;
use App\Models\Timeline;
use App\Panel\ScheduledConference\Livewire\RegistrantAttendance;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ParticipantAttendance extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms, InteractsWithInfolists;

    protected static string $resource = RegistrantResource::class;

    protected static string $view = 'panel.scheduledConference.resources.registrant-resource.pages.participant-attendance';

    public Registration $registration;

    public function mount(?Registration $record): void
    {
        $this->registration = $record;
    }

    public function getTitle(): string|Htmlable
    {
        return $this->registration->user->full_name;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
            $resource::getUrl('attendance', ['record' => $this->registration]) => 'Attendance',
        ];

        return $breadcrumbs;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make()
                    ->tabs(function () {
                        $components = [];
                        $timelines = Timeline::query()
                            ->whereHas('sessions')
                            ->orWhere('require_attendance', true)
                            ->with(['sessions'])
                            ->get();

                        foreach ($timelines as $timeline) {
                            $components[] = Tab::make($timeline->name)
                                ->badge(fn () => $timeline->sessions->count())
                                ->schema([
                                    LivewireEntry::make($timeline->name)
                                        ->livewire(RegistrantAttendance::class, [
                                            'registration' => $this->registration,
                                            'timeline' => $timeline,
                                        ]),
                                ]);
                        }

                        return $components;
                    }),
            ]);
    }
}
