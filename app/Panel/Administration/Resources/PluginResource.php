<?php

namespace App\Panel\Administration\Resources;

use App\Facades\Plugin as FacadesPlugin;
use App\Models\Plugin;
use App\Panel\Administration\Resources\PluginResource\Pages;
use App\Tables\Columns\IndexColumn;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PluginResource extends Resource
{
    protected static ?string $model = Plugin::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    public static function getNavigationLabel(): string
    {
        return __('general.plugin');
    }

    public static function getModelLabel(): string
    {
        return __('general.plugin');
    }


    public static function getNavigationGroup(): string
    {
        return __('general.settings');
    }


    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->hidden(false);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IndexColumn::make('no'),
                TextColumn::make('name')
                    ->label(__('general.name'))
                    ->wrap()
                    ->sortable()
                    ->searchable()
                    ->description(fn (Plugin $record) => $record->description)
                    ->weight(fn (Plugin $record) => $record->plugin->isEnabled() ? FontWeight::SemiBold : FontWeight::Light)
                    ->url(fn (Plugin $record) => $record->plugin->isEnabled() ? $record->plugin?->getPluginPage() : null)
                    ->color(fn (Plugin $record) => ($record->plugin->isEnabled() && $record->plugin?->getPluginPage()) ? 'primary' : null),
                TextColumn::make('author')
                    ->label(__('general.author')),
                ToggleColumn::make('enabled')
                    ->label(__('general.enabled'))
                    ->visible(auth()->user()->can('Plugin:update'))
                    ->getStateUsing(fn (Plugin $record) => $record->plugin->isEnabled())
                    ->updateStateUsing(function (Plugin $record, $state) {
                        $record->plugin->enable($state);

                        return $state;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->authorize(fn(Plugin $record) => auth()->user()->can('delete', $record))
                        ->action(function (Plugin $record) {
                            FacadesPlugin::uninstall($record->id);
                        }),
                ]),
                // TODO : Add actions based on plugin. Currently there's no way to create a dinamically action

            ])
            ->emptyStateActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePlugins::route('/'),
        ];
    }
}
