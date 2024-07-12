<?php

namespace App\Panel\Conference\Livewire\Workflows\Concerns;

trait CanModifySetting
{
    public array $settings = [];

    public function getSetting(string $key, mixed $default = false): mixed
    {
        return $this->scheduledConference->getMeta("workflow.{$this->stage}.{$key}", $default);
    }

    public function updateSetting(string $key, mixed $value): void
    {
        $this->scheduledConference->setMeta("workflow.{$this->stage}.{$key}", $value);
    }
}
