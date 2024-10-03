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
    ];

    public function getRows()
    {
        return FacadesPlugin::getRegisteredPlugins()
            ->map(function ($pluginInfo, $pluginDir) {
                $data = Arr::only($pluginInfo, ['folder', 'name', 'author', 'description', 'version']);

                $data['id'] = $pluginInfo['folder'];
                $data['enabled'] = FacadesPlugin::getSetting($pluginInfo['folder'], 'enabled');
                $data['path'] = $pluginDir;
                
                return $data;
            })
            ->values()
            ->toArray();
    }

    protected function sushiShouldCache()
    {
        return false;
    }
}
