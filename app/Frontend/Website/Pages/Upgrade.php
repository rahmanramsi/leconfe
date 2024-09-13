<?php

namespace App\Frontend\Website\Pages;

use App\Actions\Leconfe\UpgradeAction;
use App\Facades\MetaTag;
use App\Http\Middleware\SetLocale;
use App\Utils\Installer;
use Livewire\Attributes\Title;
use App\Utils\PermissionChecker;
use Illuminate\Support\Facades\App;
use App\Livewire\Forms\InstallationForm;
use App\Http\Middleware\SetupDefaultData;
use Rahmanramsi\LivewirePageGroup\Pages\Page;
use Jackiedo\Timezonelist\Facades\Timezonelist;
use Livewire\Mechanisms\ComponentRegistry;

class Upgrade extends Page
{
    protected static string $view = 'frontend.website.pages.upgrade';

    protected static string|array $withoutRouteMiddleware = [
        SetLocale::class,
        SetupDefaultData::class,
    ];

    public function mount()
    {       
        MetaTag::add('robots', 'noindex, nofollow');

        if(version_compare(app()->getInstalledVersion(), app()->getCodeVersion(), '>=')){
            return redirect('/');
        }
    }

    protected function getViewData(): array
    {
        return [
            'installedVersion' => app()->getInstalledVersion(),
            'codeVersion' => app()->getCodeVersion(),
        ];
    }

    public static function getLayout(): string
    {
        return 'frontend.website.components.layouts.base';
    }

    public function upgrade()
    {
        try {
            UpgradeAction::run();
    
            return redirect()->route('livewirePageGroup.website.pages.installation-successful');
        } catch (\Throwable $th) {
            $this->addError('upgrade', $th->getMessage());
        }

    }
}
