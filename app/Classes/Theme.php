<?php

namespace App\Classes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

abstract class Theme extends Plugin
{
	public function activate(): void
	{
		$this->loadViews();
	}

	protected function loadViews(): void
	{
		$this->assertPublicAssetsPath();

		$viewPath = $this->getPluginPath('views');

		$viewFinder = View::getFinder();
		$viewFinder->prependLocation("{$viewPath}");

		Config::set('view.paths', array_merge([$viewPath], Arr::wrap(Config::get('view.paths'))));

		$this->loadVendorViews();
	}

	protected function loadVendorViews(): void
	{
		$vendorViewsPath = $this->getPluginPath('views/vendor');

		if (file_exists($vendorViewsPath)) {
			$directories = glob($vendorViewsPath . '/*', GLOB_ONLYDIR);
			if ($directories) {
				foreach ($directories as $path) {
					View::prependNamespace(basename($path), $path);
				}
			}
		}
	}

	public function getFormSchema(): array
	{
		return [];
	}

	public function getFormData(): array
	{
		return [];
	}

	public function saveFormData(array $data): void
	{
		// Implement this method to save the form data
	}
}
