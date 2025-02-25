<?php

namespace App\Panel\ScheduledConference\Livewire\Wizards;

use App\Models\Submission;
use App\Panel\ScheduledConference\Livewire\Wizards\SubmissionWizard\Steps\ContributorsStep;
use App\Panel\ScheduledConference\Livewire\Wizards\SubmissionWizard\Steps\DetailStep;
use App\Panel\ScheduledConference\Livewire\Wizards\SubmissionWizard\Steps\ReviewStep;
use App\Panel\ScheduledConference\Livewire\Wizards\SubmissionWizard\Steps\UploadFilesStep;
use Livewire\Component;

class SubmissionWizard extends Component
{
    public Submission $record;

    public function render()
    {
        return view('panel.scheduledConference.livewire.wizards.submission-wizard');
    }

    public function steps(): array
    {
        return [
            'detail' => DetailStep::class,
            'upload-files' => UploadFilesStep::class,
            'authors' => ContributorsStep::class,
            'review' => ReviewStep::class,
        ];
    }

    public function getStartStep(): int
    {
        $queryStringStep = request()->query('step');

        foreach ($this->getStepKeys() as $index => $step) {
            if ($step !== $queryStringStep) {
                continue;
            }

            return $index + 1;
        }

        return 1;
    }

    public function getStepKeys()
    {
        return array_keys($this->steps());
    }
}
