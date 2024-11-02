<?php

namespace App\Managers;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class MetaTagManager
{
    protected array $metas = [];

    public function add(string $name, ?string $content): self
    {
        $this->metas[] = [
            'name' => $name,
            'content' => e($content),
        ];

        return $this;
    }

    public function all(): Collection
    {
        return collect($this->metas);
    }

    public function render(): HtmlString
    {
        return new HtmlString(
            $this->all()
                ->map(function ($meta) {
                    $name = $meta['name'];
                    $content = $meta['content'];

                    return <<<HTML
                        <meta name="{$name}" content="$content">
                    HTML;
                })
                ->implode("\n")
        );
    }
}
