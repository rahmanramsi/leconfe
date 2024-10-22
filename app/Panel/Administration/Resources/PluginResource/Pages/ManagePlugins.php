<?php

namespace App\Panel\Administration\Resources\PluginResource\Pages;

use App\Facades\Plugin as PluginFacade;
use App\Models\Plugin as PluginModel;
use App\Panel\Administration\Resources\PluginResource;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ManagePlugins extends ManageRecords
{
    protected static string $resource = PluginResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('upload-plugin')
                ->label(__('general.upload_plugin'))
                ->modalHeading(__('general.upload_plugin'))
                ->visible(fn () => auth()->user()->can('install', PluginModel::class))
                ->form([
                    FileUpload::make('file')
                        ->label(__('general.file'))
                        ->disk('plugins-tmp')
                        ->acceptedFileTypes(['application/zip'])
                        ->required(),
                ])
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

                    Notification::make('install-success')
                        ->title(__('general.install_success'))
                        ->success()
                        ->body(__('general.plugin_installed_successfully'))
                        ->send();
                })
                ->modalSubmitActionLabel(__('general.submit')),
        ];
    }
}
