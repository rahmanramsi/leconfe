<?php

namespace App\Panel\ScheduledConference\Livewire\Workflows\Base;

use App\Panel\ScheduledConference\Livewire\Workflows\Concerns\CanOpenStage;
use App\Panel\ScheduledConference\Livewire\Workflows\Concerns\InteractWithTenant;
use Livewire\Component;

abstract class WorkflowStage extends Component
{
    use CanOpenStage, InteractWithTenant;

    protected ?string $stage = null;

    protected ?string $stageLabel = null;

    public function getStageLabel(): ?string
    {
        return $this->stageLabel;
    }

    public function getStage(): ?string
    {
        return $this->stage;
    }
}
