<?php

namespace App\Classes;

use App\Facades\Plugin as FacadesPlugin;
use App\Interfaces\HasPlugin;
use Filament\Panel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Symfony\Component\Yaml\Yaml;

abstract class Plugin implements HasPlugin
{
    protected array $info;

    protected string $pluginPath;

    public function boot()
	{
        // Implement this method to run your plugin
    }

    public function load(): void
    {
        $this->info = Yaml::parseFile($this->getPluginInformationPath());

        View::addNamespace($this->getInfo('folder'), $this->getPluginPath('resources/views'));
    }

    public function getInfo(?string $key = null)
    {
        if ($key) {
            return $this->info[$key] ?? null;
        }

        return $this->info;
    }

    public function getPluginPath(?string $path = null)
    {
        return $this->pluginPath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function getPluginInformationPath()
    {
        return $this->getPluginPath('index.yaml');
    }

    public function setPluginPath($path): void
    {
        $this->pluginPath = $path;
    }

    public function getSetting($key, $default = null): mixed
    {
        return FacadesPlugin::getSetting($this->getInfo('folder'), $key, $default);
    }

    public function updateSetting($key, $value): mixed
    {
        return FacadesPlugin::updateSetting($this->getInfo('folder'), $key, $value);
    }

    public function onPanel(Panel $panel): void
    {
        // Implement this method to add your plugin to panel
    }

    public function onFrontend(PageGroup $frontend): void
    {
        // Implement this method to add your plugin to frontend
    }

    public function getPluginPage(): ?string
    {
        return null;
    }


	/**
	 * Create public assets directory path.
	 */
	public function enablePublicAsset(): void
	{
		$pluginAssetPath = $this->getPluginPath('public');
		if (file_exists($pluginAssetPath)) {
			$publicPluginAssetPath = public_path($this->getAssetsPath());

			// Create target symlink public theme assets directory if required
			if (! file_exists($publicPluginAssetPath)) {
				app(Filesystem::class)->relativeLink($pluginAssetPath, rtrim($publicPluginAssetPath, '/'));
			}
		}
	}

	public function getAssetsPath(?string $path = null): string
	{
		return 'plugin/' . mb_strtolower($this->getInfo('folder')) . ($path ? '/' . $path : '');
	}

	/**
     * Get theme's asset url.
     */
    public function asset(string $asset, bool $absolute = true): string
    {
        return $this->url($asset, $absolute);
    }

    /**
     * Get theme asset url.
     */
    public function url(string $url, bool $absolute = true): string
    {
        $url = trim($url, '/');

        // return external URLs unmodified
        if (URL::isValidUrl($url)) {
            return $url;
        }

        // Check into Vite manifest file
        $manifesPath = $this->getAssetsPath('manifest.json');
        if (file_exists($manifesPath)) {
            $manifest = file_get_contents($manifesPath);
            $manifest = json_decode($manifest, true);

            if (array_key_exists($url, $manifest)) {
                // Lookup asset in current's theme assets path
                $fullUrl = $this->getAssetsPath($manifest[$url]['file']);

                return $absolute ? asset($fullUrl) : $fullUrl;
            }
        }

        // Lookup asset in current's theme assets path
        $fullUrl = $this->getAssetsPath($url);

        return $absolute ? asset($fullUrl) : $fullUrl;
    }
}
