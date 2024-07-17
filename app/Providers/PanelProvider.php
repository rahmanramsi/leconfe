<?php

namespace App\Providers;

use App\Facades\Plugin;
use App\Facades\Setting;
use App\Http\Middleware\IdentifyConference;
use App\Http\Middleware\IdentifySeries;
use App\Http\Middleware\MustVerifyEmail;
use App\Http\Middleware\PanelAuthenticate;
use App\Panel\Administration\Pages\Profile;
use App\Panel\Conference\Pages\Dashboard;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TimePicker;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Forms\Components\TinyEditor;


class PanelProvider extends ServiceProvider
{
    public function scheduledConference(Panel $panel): Panel
    {
        $this->setupPanel($panel)
            ->id('scheduledConference')
            ->path('{conference:path}/scheduled-conference/{serie:path}/panel')
            ->bootUsing(fn () => static::setupFilamentComponent())
            ->homeUrl(fn () => app()->getCurrentScheduledConference()?->getHomeUrl())
            ->discoverResources(in: app_path('Panel/ScheduledConference/Resources'), for: 'App\\Panel\\ScheduledConference\\Resources')
            ->discoverPages(in: app_path('Panel/ScheduledConference/Pages'), for: 'App\\Panel\\ScheduledConference\\Pages')
            ->discoverWidgets(in: app_path('Panel/ScheduledConference/Widgets'), for: 'App\\Panel\\ScheduledConference\\Widgets')
            ->discoverLivewireComponents(in: app_path('Panel/ScheduledConference/Livewire'), for: 'App\\Panel\\ScheduledConference\\Livewire')
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn () => view('panel.scheduledConference.hooks.topbar'),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => view('panel.scheduledConference.hooks.sidebar-nav-start'),
            )
            ->middleware([
                IdentifyConference::class,
                IdentifySeries::class,
                ...static::getMiddleware(),
            ], true)
            ->authMiddleware(static::getAuthMiddleware(), true);

        Plugin::getPlugins()->each(function ($plugin) use ($panel) {
            $plugin->onPanel($panel);
        });

        return $panel;
    }

    public function conferencePanel(Panel $panel): Panel
    {
        $this->setupPanel($panel)
            ->id('conference')
            ->default()
            ->path('{conference:path}/panel')
            ->bootUsing(fn () => static::setupFilamentComponent())
            ->homeUrl(fn () => route('livewirePageGroup.conference.pages.home', ['conference' => app()->getCurrentConference()]))
            ->discoverResources(in: app_path('Panel/Conference/Resources'), for: 'App\\Panel\\Conference\\Resources')
            ->discoverPages(in: app_path('Panel/Conference/Pages'), for: 'App\\Panel\\Conference\\Pages')
            ->discoverWidgets(in: app_path('Panel/Conference/Widgets'), for: 'App\\Panel\\Conference\\Widgets')
            ->discoverLivewireComponents(in: app_path('Panel/Conference/Livewire'), for: 'App\\Panel\\Conference\\Livewire')
            ->pages([
                Dashboard::class,
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn () => view('panel.conference.hooks.topbar'),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => view('panel.conference.hooks.sidebar-nav-start'),
            )
            ->middleware([
                IdentifyConference::class,
                ...static::getMiddleware(),
            ], true)
            ->authMiddleware(static::getAuthMiddleware(), true);

        Plugin::getPlugins()->each(function ($plugin) use ($panel) {
            $plugin->onPanel($panel);
        });

        return $panel;
    }

    public function administrationPanel(Panel $panel): Panel
    {
        $this->setupPanel($panel)
            ->id('administration')
            ->path('administration')
            ->homeUrl(fn () => route('livewirePageGroup.website.pages.home'))
            ->bootUsing(function () {
                static::setupFilamentComponent();
            })
            ->discoverResources(in: app_path('Panel/Administration/Resources'), for: 'App\\Panel\\Administration\\Resources')
            ->discoverPages(in: app_path('Panel/Administration/Pages'), for: 'App\\Panel\\Administration\\Pages')
            ->discoverWidgets(in: app_path('Panel/Administration/Widgets'), for: 'App\\Panel\\Administration\\Widgets')
            ->discoverLivewireComponents(in: app_path('Panel/Administration/Livewire'), for: 'App\\Panel\\Administration\\Livewire')
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => view('panel.administration.hooks.sidebar-nav-start'),
            )
            ->middleware(static::getMiddleware(), true)
            ->authMiddleware(static::getAuthMiddleware(), true);

        Plugin::getPlugins()->each(function ($plugin) use ($panel) {
            $plugin->onPanel($panel);
        });

        return $panel;
    }

    public function setupPanel(Panel $panel): Panel
    {
        return $panel
            ->maxContentWidth('full')
            ->renderHook(
                'panels::scripts.before',
                fn () => Blade::render(<<<'Blade'
                        @vite(['resources/panel/js/panel.js'])
                    Blade)
            )
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->viteTheme('resources/panel/css/panel.css')
            ->colors([
                'primary' => Color::hex('#1c3569'),
            ])
            ->userMenuItems([
                'logout' => MenuItem::make()
                    ->url(fn (): string => route('logout')),
                'profile' => MenuItem::make()
                    ->url(fn (): string => Profile::getUrl()),
            ])
            ->darkMode(false)
            ->databaseNotifications()
            ->databaseNotificationsPolling(null);
    }

    public function register(): void
    {
        Filament::registerPanel(
            fn (): Panel => $this->scheduledConference(Panel::make()),
        );

        Filament::registerPanel(
            fn (): Panel => $this->conferencePanel(Panel::make()),
        );

        Filament::registerPanel(
            fn (): Panel => $this->administrationPanel(Panel::make()),
        );

        // $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/panel/conference/components'), 'panel');
        Blade::anonymousComponentPath(resource_path('views/panel/administration/components'), 'administration');
        Blade::anonymousComponentPath(resource_path('views/panel/series/components'), 'series');
    }

    public static function getMiddleware(): array
    {
        return [
            'web',
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
            'logout.banned',
        ];
    }

    public static function getAuthMiddleware(): array
    {
        return [
            PanelAuthenticate::class,
            MustVerifyEmail::class,
        ];
    }

    public static function setupFilamentComponent()
    {
        // TODO Validasi file type menggunakan format extension, bukan dengan mime type,
        // hal ini agar mempermudah pengguna dalam melakukan setting file apa saja yang diperbolehkan
        // Saat ini SpatieMediaLibraryFileUpload hanya support file validation dengan mime type.
        // Solusi mungkin buat custom component upload dengan menggunakan library seperti dropzone, atau yang lainnya.
        SpatieMediaLibraryFileUpload::configureUsing(function (SpatieMediaLibraryFileUpload $fileUpload): void {
            $fileUpload
                ->imageResizeTargetWidth(2048)
                ->imageResizeTargetWidth(2048)
                ->imageResizeMode('contain')
                ->imageResizeUpscale(false)
                ->maxSize(config('media-library.max_file_size') / 1024);

            // ->acceptedFileTypes(config('media-library.accepted_file_types'))
        });
        DatePicker::configureUsing(function (DatePicker $datePicker): void {
            $datePicker
                ->native(false)
                ->displayFormat(Setting::get('format_date'));
        });

        TimePicker::configureUsing(function (TimePicker $timePicker): void {
            $timePicker->displayFormat(Setting::get('format_time'));
        });

        Table::configureUsing(function (Table $table): void {
            $table
                ->defaultPaginationPageOption(5)
                ->paginationPageOptions([5, 10, 25, 50]);
        });

        TinyEditor::configureUsing(function (TinyEditor $tinyEditor): void {
            $tinyEditor
                ->setRelativeUrls(false)
                ->setRemoveScriptHost(false)
                ->toolbarSticky(false);
        });
    }
}
