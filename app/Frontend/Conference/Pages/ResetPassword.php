<?php

namespace App\Frontend\Conference\Pages;

use App\Frontend\Website\Pages\ResetPassword as Page;

class ResetPassword extends Page
{
    public function getBreadcrumbs(): array
    {
        return [
            app()->getCurrentConference()->getHomeUrl() => __('general.home'),
            __('general.reset_password'),
        ];
    }
}
