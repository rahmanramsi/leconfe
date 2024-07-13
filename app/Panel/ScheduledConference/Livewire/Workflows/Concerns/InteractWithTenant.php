<?php

namespace App\Panel\ScheduledConference\Livewire\Workflows\Concerns;

use App\Models\Conference;
use App\Models\ScheduledConference;

trait InteractWithTenant
{
    public ScheduledConference $scheduledConference;

    public function __construct()
    {
        $this->scheduledConference = app()->getCurrentScheduledConference();
    }
}
