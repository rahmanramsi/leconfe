<?php

namespace App\Models;

use App\Facades\Plugin as FacadesPlugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Sushi\Sushi;

class Plugin extends Model
{
    use Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $schema = [
        'id' => 'integer',
        'plugin_name' => 'string',
        'author' => 'string',
        'description' => 'string',
        'version' => 'string',
        'enabled' => 'boolean',
        'path' => 'string',
        'type' => 'string',
    ];

    public function getRows()
    {
        return FacadesPlugin::getRegisteredPlugins()
            ->map(function ($pluginInfo, $pluginDir) {
                $data = Arr::only($pluginInfo, ['folder', 'name', 'author', 'description', 'version', 'type']);

                $data['id']         = $pluginInfo['folder'];
                $data['enabled']    = FacadesPlugin::getSetting($pluginInfo['folder'], 'enabled');
                $data['path']       = $pluginDir;
                $data['type']       ??= 'plugin';
                return $data;
            })
            ->values()
            ->toArray();
    }


    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeDisabled($query)
    {
        return $query->where('enabled', false);
    }

    public function scopeTheme($query)
    {
        return $query->where('type', 'theme');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    protected function sushiShouldCache()
    {
        return false;
    }
}
