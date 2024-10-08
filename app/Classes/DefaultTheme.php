<?php

namespace App\Classes;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\View;
use luizbills\CSS_Generator\Generator as CSSGenerator;
use matthieumastadenis\couleur\ColorFactory;
use matthieumastadenis\couleur\ColorSpace;

class DefaultTheme extends Theme
{
	public function __construct()
	{
		$this->pluginPath = __DIR__;
	}

	public function load(): static
	{
		$this->info = $this->loadInformation();

		return $this;
	}

	public function onActivate(): void
	{
		switch (true) {
			case $currentScheduledConference = app()->getCurrentScheduledConference():
				$appearanceColor = $currentScheduledConference->getMeta('appearance_color');
				break;
			
			case $currentConference = app()->getCurrentConference():
				$appearanceColor = $currentConference->getMeta('appearance_color');
				break;

			default:
				$site = app()->getSite();
				$appearanceColor = $site->getMeta('appearance_color');
				break;
		}	


        if ($appearanceColor = $currentScheduledConference->getMeta('appearance_color')) {
            $oklch = ColorFactory::new($appearanceColor)->to(ColorSpace::OkLch);
            $css = new CSSGenerator();
            $css->root_variable('p', "{$oklch->lightness}% {$oklch->chroma} {$oklch->hue}");

            View::share('appearanceColor', $css->get_output());
        }

	}


	public function loadInformation()
	{
		return [
			"name" => "Default Theme",
			"folder" => "DefaultTheme",
			"author" => "Leconfe",
			"description" => "Default Theme for Leconfe",
			"version" => "1.0.0",
			"type" => "theme",
		];
	}
	
	public function getFormSchema(): array
	{
		return [
			ColorPicker::make('appearance_color')
				->regex('/^#?(([a-f0-9]{3}){1,2})$/i')
				->label(__('general.appearance_color')),
		];
	}

	public function getFormData(): array
	{
		return [
			'appearance_color' => $this->getSetting('appearance_color'),
		];
	}

	public function isHidden(): bool
	{
		return true;
	}

	public function isEnabled(): bool
	{
		return true;
	}

	public function canBeDisabled(): bool
	{
		return false;
	}
}
