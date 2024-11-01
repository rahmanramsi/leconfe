<?php

namespace App\Panel\Administration\Pages;

use App\Infolists\Components\LivewireEntry;
use App\Panel\Administration\Livewire\PluginGalleryTable;
use App\Panel\Administration\Livewire\PluginTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Infolists\Components\Tabs;
use App\Facades\Plugin as PluginFacade;
use App\Models\Plugin;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Log;

class PluginManagement extends Page implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static string $view = 'panel.administration.pages.plugin-management';

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): string
    {
        return __('general.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.plugin');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload-plugin')
                ->label(__('general.upload_plugin'))
                ->modalHeading(__('general.upload_plugin'))
                ->visible(fn() => auth()->user()->can('install', Plugin::class))
                ->form([
                    FileUpload::make('file')
                        ->label(__('general.file'))
                        ->disk('plugins-tmp')
                        ->acceptedFileTypes(['application/zip'])
                        ->required(),
                ])
                ->modalWidth(MaxWidth::ExtraLarge)
                ->action(function (array $data) {

                    try {
                        PluginFacade::install(PluginFacade::getTempDisk()->path($data['file']));
                    } catch (\Throwable $th) {
                        Notification::make('install-failed')
                            ->danger()
                            ->title(__('general.failed_to_install_plugin'))
                            ->send();
                        Log::error($th);

                        return;
                    } finally {
                        PluginFacade::getTempDisk()->delete($data['file']);
                    }

                    $this->dispatch('refresh-table');
                    
                    Notification::make('install-success')
                        ->title(__('general.install_success'))
                        ->success()
                        ->body(__('general.plugin_installed_successfully'))
                        ->send();
                })
                ->modalSubmitActionLabel(__('general.submit')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Installed Plugins')
                            ->schema([
                                LivewireEntry::make('plugin-installed')
                                    ->livewire(PluginTable::class),
                            ]),
                        Tabs\Tab::make('Plugin Gallery')
                            ->schema([
                                LivewireEntry::make('plugin-gallery')
                                    ->livewire(PluginGalleryTable::class)
                                    ->lazy(),
                            ]),
                    ])
                    ->contained(false)
            ]);
    }
}
