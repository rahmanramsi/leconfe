<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\Conference;
use Squire\Models\Country;
use Illuminate\Support\Arr;
use App\Models\Enums\UserRole;
use Filament\Facades\Filament;
use App\Actions\User\UserCreateAction;
use App\Facades\Setting;
use Rahmanramsi\LivewirePageGroup\Pages\Page;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Contracts\Support\Htmlable;
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
