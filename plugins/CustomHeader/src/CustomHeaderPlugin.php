<?php

namespace CustomHeader;

use App\Classes\Plugin;
use App\Facades\Hook;
use CustomHeader\Pages\CustomHeaderPage;
use Filament\Panel;

class CustomHeaderPlugin extends Plugin
{
	public function boot()
	{
		$headerContent = $this->getSetting('header_content');
		$footerContent = $this->getSetting('footer_content');

		if ($headerContent) {
			Hook::add('Frontend::Views::Head', function ($hookName, &$output) use ($headerContent) {
				$output .= $headerContent;
			});
		}

		if ($footerContent) {
			Hook::add('Frontend::Views::Footer', function ($hookName, &$output) use ($footerContent) {
				$output .= $footerContent;
			});
		}
	}

	public function onPanel(Panel $panel): void
	{
		$panel->pages([
			CustomHeaderPage::class,
		]);
	}

	public function getPluginPage(): ?string
	{
		try {
			return CustomHeaderPage::getUrl();
		} catch (\Throwable $th) {
			return null;
		}
	}
}
