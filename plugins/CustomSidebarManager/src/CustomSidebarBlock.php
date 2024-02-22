<?php

namespace CustomSidebarManager;

use App\Livewire\Block;

class CustomSidebarBlock extends Block
{
    protected ?string $content;

    protected ?string $view = 'CustomSidebarManager::custom-sidebar';

    public function __construct(string $name, ?string $content)
    {
        $this->name = $name;
        $this->content = $content;
    }

    public function getViewData(): array
    {
        return [
            'id' => $this->getDatabaseName(),
            'name' => $this->name,
            'content' => $this->content,
        ];
    }

    public function getBlockName(): string
    {
        return $this->name.' (Custom Sidebar)';
    }
}
