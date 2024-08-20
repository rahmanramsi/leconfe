<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions;

use Livewire\Component;
use App\Models\Timeline;
use App\Models\Submission;
use App\Models\Enums\UserRole;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class Payment extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Submission $submission;

    protected $listeners = [
        'refreshSubmission' => '$refresh',
    ];

    public function mount(Submission $submission)
    {
    }

    public function render()
    {
        $submissionParticipant = $this->submission
            ->participants()
            ->whereHas('role', fn (Builder $query) => $query->where('name', UserRole::Author->value))
            ->where('user_id', auth()->user()->id)
            ->limit(1)
            ->first();

        return view('panel.scheduledConference.livewire.submissions.payment', [
            'currentScheduledConference' => app()->getCurrentScheduledConference(),
            'submissionRegistration' => $this->submission->registration,
            'submissionRegistrant' => $this->submission->registration->user ?? null,
            'isSubmissionAuthor' => $submissionParticipant !== null,
            'isRegistrationOpen' => Timeline::isRegistrationOpen(),
        ]);
    }
}
