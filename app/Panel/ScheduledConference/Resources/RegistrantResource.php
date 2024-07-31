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
use App\Models\ScheduledConference;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
use Filament\Resources\Components\Tab;
use Filament\Forms\Components\Checkbox;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\RegistrationStatus;
use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationGroup;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\Group as FormGroup;
use AnourValar\EloquentSerialize\Tests\Models\Post;
use App\Models\Enums\RegistrationPaymentType;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;
use App\Panel\ScheduledConference\Resources\RegistrantResource\RelationManagers;

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
                            ->options(
                                Arr::except(RegistrationStatus::array(), RegistrationStatus::Trashed->value)
                            )
                            ->native(false)
                            ->required()
                            ->live(),
                        DatePicker::make('paid_at')
                            ->label('Paid Date')
                            ->placeholder('Select registration paid date..')
                            ->prefixIcon('heroicon-m-calendar')
                            ->formatStateUsing(fn () => now())
                            ->visible(fn (Get $get): bool => $get('state') === RegistrationStatus::Paid->value)
                            ->required(),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (?array $data) {
                        if($data['state'] !== RegistrationStatus::Paid->value) {
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
            ->heading('Registrant List')
            ->headerActions([
                Action::make('Enroll User')
                    ->url(fn () => RegistrantResource::getUrl('enroll'))
                    ->authorize('Registrant:enroll')
            ])
            ->columns([
                SpatieMediaLibraryImageColumn::make('user.profile')
                    ->label('Avatar')
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
                        if($record->registrationPayment->currency === 'free') {
                            return 'Free';
                        }

                        $code = Str::upper($record->registrationPayment->currency);
                        $cost = money($record->registrationPayment->cost, $record->registrationPayment->currency);

                        return "($code) $cost";
                    }),
                TextColumn::make('registrationPayment.state')
                    ->label('State')
                    ->formatStateUsing(fn (Model $record) => $record->getStatus())
                    ->badge()
                    ->color(fn (Model $record) => RegistrationStatus::from($record->getStatus())->getColor()),
                TextColumn::make('registrationPayment.paid_at')
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
                    ->label('Decision')
                    ->modalHeading('Paid Status Decision')
                    ->modalWidth('lg')
                    ->authorize('Registrant:edit'),
                ActionGroup::make([
                    Action::make('trash')
                        ->color(Color::Red)
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation()
                        ->action(function (Model $record) {
                            $record->trashed = true;
                            $record->save();
                        })
                        ->visible(fn (Model $record) => !$record->trashed)
                        ->authorize('Registrant:delete'),
                    Action::make('restore')
                        ->color(Color::Green)
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (Model $record) {
                            $record->trashed = false;
                            $record->save();
                        })
                        ->hidden(fn (Model $record) => !$record->trashed)
                        ->authorize('Registrant:edit'),
                    DeleteAction::make()
                        ->hidden(fn (Model $record) => !$record->trashed)
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

    public static function getSubNavigation(): array
    {
        $url = url()->current();

        return [
            NavigationGroup::make()
                ->items([
                    NavigationItem::make('Registration Type')
                        ->icon('heroicon-o-list-bullet')
                        ->badge(fn () => RegistrationType::where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())->count())
                        ->isActiveWhen(fn () => $url === Pages\ListTypeSummary::getUrl())
                        ->url(Pages\ListTypeSummary::getUrl()),
                    NavigationItem::make('Registrant List')
                        ->icon('heroicon-o-bars-3-bottom-left')
                        ->isActiveWhen(fn () => $url === Pages\ListRegistrants::getUrl())
                        ->url(Pages\ListRegistrants::getUrl()),
                ])
                ->extraSidebarAttributes([
                    'class' => 'bg-white p-2 rounded-xl shadow-lg outline outline-1 outline-gray-200',
                ], true)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrants::route('/'),
            'enroll' => Pages\EnrollUser::route('/enroll'),
            'type' => Pages\ListTypeSummary::route('/type'),
        ];
    }
}
