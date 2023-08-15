<?php

namespace App\Schemas;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Squire\Models\Country;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use App\Actions\User\UserCreateAction;
use App\Actions\User\UserUpdateAction;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserSchema
{
    public static function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->heading('Users')
            ->columns([
                TextColumn::make('given_name')
                    ->size('sm')
                    ->searchable(),
                TextColumn::make('family_name')
                    ->size('sm')
                    ->searchable(),
                TextColumn::make('email')
                    ->size('sm')
                    ->searchable(),
            ])
            ->filters([
                // ...
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth('2xl')
                    ->outlined()
                    ->form(fn () => static::formSchemas())
                    ->using(fn (array $data) => UserCreateAction::run($data)),
            ])
            ->actions([
                ActionGroup::make([
                    Impersonate::make()
                        ->redirectTo('panel'),
                    EditAction::make()
                        ->modalWidth('2xl')
                        ->form(fn () => static::formSchemas())
                        ->mutateRecordDataUsing(fn ($data, Model $record) => array_merge($data, ['meta' => $record->getAllMeta()->toArray()]))
                        ->using(fn (array $data, Model $record) => UserUpdateAction::run($data, $record)),
                    DeleteAction::make()
                        ->before(function (DeleteAction $action, Model $record) {
                            if ($record->given_name === 'admin') {
                                Notification::make()
                                    ->danger()
                                    ->title('You can\'t delete admin user')
                                    ->persistent()
                                    ->send();
                                $action->halt();
                            }
                        }),
                ])
            ])
            ->queryStringIdentifier('users')
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::formSchemas());
    }

    public static function formSchemas(): array
    {
        return [
            Grid::make()
                ->schema([
                    TextInput::make('given_name')
                        ->required(),
                    TextInput::make('family_name'),
                ]),
            TextInput::make('email')
                ->disabled(fn (?Model $record) => $record)
                ->dehydrated(fn (?Model $record) => !$record)
                ->unique(ignoreRecord: true),
            Grid::make()
                ->schema([
                    TextInput::make('password')
                        ->required(fn (?Model $record) => !$record)
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->confirmed(),
                    TextInput::make('password_confirmation')
                        ->requiredWith('password')
                        ->password()
                        ->dehydrated(false),
                ]),
            Select::make('country')
                ->searchable()
                ->options(Country::all()->pluck('name', 'id')),
            Section::make('User Details')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('meta.phone'),
                            TextInput::make('meta.orcid_id')
                                ->label('ORCID iD'),
                            TextInput::make('meta.affiliation'),
                        ]),
                ]),
        ];
    }
}
