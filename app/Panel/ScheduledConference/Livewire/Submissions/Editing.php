<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions;

use App\Models\Submission;

class Editing extends \Livewire\Component
{
    public Submission $submission;

    protected $listeners = [
        'refreshSubmission' => '$refresh',
    ];

    public function render()
    {
        return view('panel.scheduledConference.livewire.submissions.editing');
    }
}
