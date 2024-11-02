<?php

namespace App\Forms\Components;

use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor as BaseTinyEditor;

class TinyEditor extends BaseTinyEditor
{
    protected string $toolbar = '';

    protected string $plugins = '';

    public function toolbar(string $toolbar)
    {
        $this->toolbar = $toolbar;

        return $this;
    }

    public function getToolbar(): string
    {
        if ($toolbar = $this->evaluate($this->toolbar)) {
            return $toolbar;
        }

        return parent::getToolbar();
    }

    public function plugins(string $plugins)
    {
        $this->plugins = $plugins;

        return $this;
    }

    public function getPlugins(): string
    {
        if ($plugins = $this->evaluate($this->plugins)) {
            return $plugins;
        }

        return parent::getPlugins();
    }
}
