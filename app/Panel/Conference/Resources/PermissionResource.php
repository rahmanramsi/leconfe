<?php

namespace App\Panel\Conference\Resources;

use App\Models\Permission;
use App\Panel\Conference\Resources\PermissionResource\Pages;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 7;

    /**
     * This Resource is only for development purposes.
     */
    public static function isDiscovered(): bool
    {
        return ! app()->isProduction();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('context')
                    ->dehydrateStateUsing(fn (string $state): string => Str::studly($state))
                    ->alpha()
                    ->helperText('Context must be StudlyCase'),
                TextInput::make('action')
                    ->alpha()
                    ->helperText('Action must be camelCase')
                    ->dehydrateStateUsing(fn (string $state): string => Str::camel($state)),
                CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->deferLoading()
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->badge()
                    ->searchable(),
                TextColumn::make('context')
                    ->formatStateUsing(fn (string $state): string => Str::headline($state)),
                TextColumn::make('action')
                    ->formatStateUsing(fn (string $state): string => Str::headline($state)),
                TextColumn::make('roles_count')
                    ->label('Assigned Roles')
                    ->counts('roles')
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'primary' : 'gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (Permission $record, array $data) {
                        $data['context'] = $record->context;
                        $data['action'] = $record->action;  

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->using(function (Permission $record, Tables\Actions\DeleteAction $action) {
                        try {
                            return $record->delete();
                        } catch (\Throwable $th) {
                            $action->failureNotificationTitle($th->getMessage());
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePermissions::route('/'),
        ];
    }
}
