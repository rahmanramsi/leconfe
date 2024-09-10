<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Frontend\Website\Pages\ResetPassword as Page;

class ResetPassword extends Page
{
    public function getBreadcrumbs(): array
    {
        return [
            app()->getCurrentScheduledConference()->getHomeUrl() => __('general.home'),
            __('general.reset_password'),
        ];
    }
}
