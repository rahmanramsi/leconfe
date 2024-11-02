<?php

namespace App\Frontend\Website\Pages;

use App\Facades\MetaTag;

class InstallationSuccessful extends Page
{
    protected static string $view = 'frontend.website.pages.installation-successful';

    public function mount()
    {
        MetaTag::add('robots', 'noindex, nofollow');
    }

    public static function getLayout(): string
    {
        return 'frontend.website.components.layouts.base';
    }
}
