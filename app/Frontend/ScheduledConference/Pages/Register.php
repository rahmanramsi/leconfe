<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Frontend\Website\Pages\Register as WebsiteRegister;

class Register extends WebsiteRegister
{
	protected function getViewData(): array
    {
		$data = parent::getViewData();

		$data['scheduledConference'] = app()->getCurrentScheduledConference();

		return $data;
	}
}
