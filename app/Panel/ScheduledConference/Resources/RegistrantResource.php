<?php

namespace App\Panel\Series\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Registration;
use App\Models\RegistrationType;
use Filament\Resources\Resource;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
use Filament\Resources\Components\Tab;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteBulkAction;
use AnourValar\EloquentSerialize\Tests\Models\Post;
use App\Panel\Series\Resources\RegistrantResource\Pages;
use App\Panel\Series\Resources\RegistrantResource\RelationManagers;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

class RegistrantResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $modelLabel = 'Registration';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Conference';

    protected static ?string $navigationLabel = 'Registrant';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if($user->can('Registrant:viewAny')) {
            return true;
        }
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Checkbox::make('not_paid')
                    ->label('Set as not paid')
                    ->formatStateUsing(fn (Model $record) => $record->paid_at !== null ? false : true)
                    ->live(),
                DatePicker::make('paid_at')
                    ->label('Paid Date')
                    ->placeholder('Select registration paid date..')
                    ->prefixIcon('heroicon-m-calendar')
                    ->default(now())
                    ->visible(fn (Get $get) => (bool) !$get('not_paid'))
                    ->required()
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereSerieId(app()->getCurrentSerieId()))
            ->heading('Registrant List')
            ->headerActions([
                Action::make('Enroll User')
                    ->url(fn () => RegistrantResource::getUrl('enroll'))
                    ->authorize('Registrant:enroll')
            ])
            ->columns([
                TextColumn::make('user.given_name')
                    ->label('User')
                    ->formatStateUsing(fn (Model $record) => $record->user->given_name.' '.$record->user->family_name)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('registration_type.type')
                    ->label('Type')
                    ->description(fn (Model $record) => $record->registration_type->getCostWithCurrency()),
                TextColumn::make('is_trashed')
                    ->label('Status')
                    ->formatStateUsing(fn (Model $record) => Str::headline($record->getStatus()))
                    ->badge()
                    ->color(fn (Model $record) => match($record->getStatus()) {
                        'paid' => Color::Green,
                        'unpaid' => Color::Yellow,
                        'trash' => Color::Red,
                    }),
                TextColumn::make('paid_at')
                    ->label('Paid Date')
                    ->placeholder('Not Paid')
                    ->date('Y-M-d')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registration Date')
                    ->date('Y-M-d')
                    ->sortable(),
            ])
            ->emptyStateHeading('No Registrant')
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->label('Paid Status')
                    ->icon('heroicon-m-banknotes')
                    ->button()
                    ->modalHeading('Change paid status')
                    ->mutateFormDataUsing(function ($data) {
                        if($data['not_paid']) {
                            $data['paid_at'] = null;
                        }
                        return $data;
                    })
                    ->authorize('Registrant:edit'),
                Action::make('trash')
                    ->button()
                    ->color(Color::Red)
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    ->action(function (Model $record) {
                        $record->is_trashed = true;
                        $record->save();
                    })
                    ->visible(fn (Model $record) => !$record->is_trashed)
                    ->authorize('Registrant:delete'),
                Action::make('restore')
                    ->button()
                    ->color(Color::Green)
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->action(function (Model $record) {
                        $record->is_trashed = false;
                        $record->save();
                    })
                    ->hidden(fn (Model $record) => !$record->is_trashed)
                    ->authorize('Registrant:edit'),
                DeleteAction::make()
                    ->button()
                    ->hidden(fn (Model $record) => !$record->is_trashed)
                    ->authorize('Registrant:delete'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('Registrant:delete'),
            ])
            ->groups([
                Group::make('registration_type.type')
                    ->label('Type')
                    ->collapsible(),
            ])
            ->defaultGroup('registration_type.type');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrants::route('/'),
            'enroll' => Pages\EnrollUser::route('/enroll'),
            // 'create' => Pages\CreateRegistrant::route('/create'),
            // 'edit' => Pages\EditRegistrant::route('/{record}/edit'),
        ];
    }
}
