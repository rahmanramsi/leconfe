<?php

namespace App\Models;

use App\Facades\Plugin as FacadesPlugin;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Plugin extends Model
{
    use Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    public function getRows(): array
    {
        return FacadesPlugin::getPlugins(false)
            ->map(function ($plugin) {
                $data['id'] = $plugin->getInfo('folder');
                $data['name'] = $plugin->getInfo('name');
                $data['author'] = $plugin->getInfo('author');
                $data['description'] = $plugin->getInfo('description');
                $data['version'] = $plugin->getInfo('version');
                $data['enabled'] = $plugin->isEnabled();
                $data['path'] = $plugin->getPluginPath();
                $data['type'] = $plugin->getInfo('type') ?? 'plugin';
                $data['isHidden'] = $plugin->isHidden();
                $data['canBeDisabled'] = $plugin->canBeDisabled();
                $data['canBeEnabled'] = $plugin->canBeEnabled();

                return $data;
            })
            ->values()
            ->toArray();
    }

    protected function plugin(): Attribute
    {
        return Attribute::make(
            get: fn () => FacadesPlugin::getPlugin($this->id),
        );
    }

    public function scopeEnabled($query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeDisabled($query): Builder
    {
        return $query->where('enabled', false);
    }

    public function scopeTheme($query): Builder
    {
        return $query->where('type', 'theme');
    }

    public function scopeType($query, $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeHidden($query, $hidden = true): Builder
    {
        return $query->where('isHidden', $hidden);
    }

    protected function sushiShouldCache()
    {
        return false;
    }
}
