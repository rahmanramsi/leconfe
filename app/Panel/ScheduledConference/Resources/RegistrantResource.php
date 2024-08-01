<?php

namespace App\Panel\ScheduledConference\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Squire\Models\Currency;
use App\Models\Registration;
use Filament\Facades\Filament;
use App\Models\RegistrationType;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\RestoreAction;
use App\Models\Enums\RegistrationPaymentType;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Models\Enums\RegistrationPaymentState;
use Filament\Tables\Actions\ForceDeleteAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

class RegistrantResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $modelLabel = 'Registration';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Conference';

    protected static ?string $navigationLabel = 'Registrants';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if ($user->can('Registrant:viewAny')) {
            return true;
        }
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)
                    ->relationship('registrationPayment')
                    ->schema([
                        Select::make('state')
                            ->options(RegistrationPaymentState::array())
                            ->native(false)
                            ->required()
                            ->live(),
                        DatePicker::make('paid_at')
                            ->label('Paid Date')
                            ->placeholder('Select registration paid date..')
                            ->prefixIcon('heroicon-m-calendar')
                            ->formatStateUsing(fn () => now())
                            ->visible(fn (Get $get): bool => $get('state') === RegistrationPaymentState::Paid->value)
                            ->required(),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (?array $data) {
                        if ($data['state'] !== RegistrationPaymentState::Paid->value) {
                            $data['paid_at'] = null;
                        } else {
                            $data['type'] = RegistrationPaymentType::Manual->value;
                        }
                        return $data;
                    })
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId()))
            ->reorderable('order_column')
            ->heading('Registrant List')
            ->headerActions([
                Action::make('Enroll User')
                    ->url(fn () => RegistrantResource::getUrl('enroll'))
                    ->authorize('Registrant:enroll')
            ])
            ->columns([
                SpatieMediaLibraryImageColumn::make('user.profile')
                    ->label('Profile')
                    ->grow(false)
                    ->collection('profile')
                    ->conversion('avatar')
                    ->width(50)
                    ->height(50)
                    ->defaultImageUrl(function (Model $record): string {
                        $name = Str::of(Filament::getUserName($record->user))
                            ->trim()
                            ->explode(' ')
                            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
                            ->join(' ');

                        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=FFFFFF&background=111827&font-size=0.33';
                    })
                    ->extraCellAttributes([
                        'style' => 'width: 1px',
                    ])
                    ->circular(),
                TextColumn::make('user.full_name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('registrationPayment.name')
                    ->label('Type')
                    ->description(function (Model $record) {
                        if ($record->registrationPayment->currency === 'free') {
                            return 'Free';
                        }

                        $code = Str::upper($record->registrationPayment->currency);
                        $cost = money($record->registrationPayment->cost, $record->registrationPayment->currency);

                        return "($code) $cost";
                    }),
                TextColumn::make('registrationPayment.state')
                    ->label('State')
                    ->badge()
                    ->color(fn (Model $record) => RegistrationPaymentState::from($record->getState())->getColor()),
                TextColumn::make('deleted_at')
                    ->label('Status')
                    ->placeholder('Valid')
                    ->formatStateUsing(fn () => "Trashed")
                    ->badge()
                    ->color(Color::Red),
                TextColumn::make('created_at')
                    ->label('Registration Date')
                    ->date('Y-M-d')
                    ->sortable(),
            ])
            ->emptyStateHeading('No Registrant')
            ->filters([
                SelectFilter::make('type')
                    ->relationship('RegistrationType', 'type', modifyQueryUsing: fn ($query) => $query->where('active', '!=', false))
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                EditAction::make()
                    ->label('Decision')
                    ->modalHeading('Paid Status Decision')
                    ->modalWidth('lg')
                    ->authorize('Registrant:edit'),
                ActionGroup::make([
                    DeleteAction::make()
                        ->label('Trash')
                        ->authorize('Registrant:delete'),
                    RestoreAction::make()
                        ->color(Color::Green)
                        ->authorize('Registrant:delete'),
                    ForceDeleteAction::make()
                        ->label('Delete')
                        ->authorize('Registrant:delete'),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('Registrant:delete'),
            ])
            ->groups([
                Group::make('registrationPayment.name')
                    ->label('Type')
                    ->collapsible(),
            ])
            ->defaultGroup('registrationPayment.name');
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
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
