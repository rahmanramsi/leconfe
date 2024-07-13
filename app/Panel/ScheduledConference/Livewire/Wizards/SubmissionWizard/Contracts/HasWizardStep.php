<?php

namespace App\Panel\ScheduledConference\Livewire\Wizards\SubmissionWizard\Contracts;

interface HasWizardStep
{
    public static function getWizardLabel(): string;
}
