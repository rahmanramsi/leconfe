<?php

namespace App\Panel\ScheduledConference\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use App\Facades\Setting;
use App\Models\Timeline;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Registration;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\View;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\RestoreAction;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Enums\RegistrationPaymentType;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Models\Enums\RegistrationPaymentState;
use App\Models\RegistrationAttendance;
use Filament\Tables\Actions\ForceDeleteAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class RegistrantResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $modelLabel = 'Registration';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Conference';

    protected static ?string $navigationLabel = 'Registrants';

    public static function canAccess(): bool
    {
        return auth()->user()->can('viewAny', Registration::class);
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
                            ->formatStateUsing(fn() => now())
                            ->visible(fn(Get $get): bool => $get('state') === RegistrationPaymentState::Paid->value)
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
            ->modifyQueryUsing(fn(Builder $query) => $query->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId()))
            ->heading('Registrant List')
            ->headerActions([
                Action::make('attendance_qr_code')
                    ->label('Attendance QR Code')
                    ->icon('heroicon-m-qr-code')
                    ->color('gray')
                    ->modalHeading(app()->getCurrentScheduledConference()->title)
                    ->modalDescription('Attendance QR Code')
                    ->modalSubmitAction(false)
                    ->infolist(function (Infolist $infolist): Infolist {
                        return $infolist
                            ->record(app()->getCurrentScheduledConference())
                            ->schema([
                                Split::make([
                                    View::make('blade')
                                        ->view('panel.scheduledConference.resources.registrant-resource.pages.attendance-qr-code', [
                                            'currentScheduledConference' => app()->getCurrentScheduledConference(),
                                            'attendanceRedirectUrl' => route('livewirePageGroup.scheduledConference.pages.agenda'),
                                            'QrCodeImageSize' => 400,
                                            'QrCodeFooterText' => 'Please scan this QR Code to confirm your attendance.',
                                        ]),
                                    Fieldset::make('')
                                        ->schema([
                                            TextEntry::make('title'),
                                            TextEntry::make('description')
                                                ->getStateUsing(fn(Model $record) => $record->getMeta('description'))
                                                ->placeholder('Description Empty')
                                                ->lineClamp(8),
                                            InfolistGrid::make(2)
                                                ->schema([
                                                    TextEntry::make('date_start')
                                                        ->date(Setting::get('format_date')),
                                                    TextEntry::make('date_end')
                                                        ->date(Setting::get('format_date')),
                                                ]),
                                        ])
                                        ->columns(1),
                                ])
                            ])
                            ->columns(1);
                    })
                    ->visible(fn() => app()->getCurrentScheduledConference()->isAttendanceEnabled()),
                Action::make('enroll_user')
                    ->label('Enroll User')
                    ->url(fn() => RegistrantResource::getUrl('enroll'))
                    ->authorize('enroll', Registration::class)
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
                            ->map(fn(string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
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

                        $cost = money($record->registrationPayment->cost, $record->registrationPayment->currency, true);

                        return $cost;
                    }),
                TextColumn::make('registrationPayment.state')
                    ->label('State')
                    ->badge()
                    ->color(fn (Model $record) => RegistrationPaymentState::from($record->getState())->getColor()),
                TextColumn::make('created_at')
                    ->label('Registration Date')
                    ->date(Setting::get('format_date'))
                    ->sortable(),
            ])
            ->emptyStateHeading('No Registrant')
            ->filters([
                SelectFilter::make('type')
                    ->relationship('RegistrationType', 'type', modifyQueryUsing: fn($query) => $query->where('active', '!=', false))
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                EditAction::make()
                    ->label('Decision')
                    ->modalHeading('Paid Status Decision')
                    ->modalWidth('lg')
                    ->hidden(fn(Model $record) => $record->trashed())
                    ->authorize(fn (Model $record) => auth()->user()->can('update', $record)),
                ActionGroup::make([
                    Action::make('attendance')
                        ->label('Attendance')
                        ->icon('heroicon-m-calendar-days')
                        ->color(Color::Blue)
                        ->url(fn(Model $record) => static::getUrl('attendance', ['record' => $record]))
                        ->visible(fn(Model $record) => ($record->registrationPayment->state === RegistrationPaymentState::Paid->value) && app()->getCurrentScheduledConference()->isAttendanceEnabled())
                        ->authorize(fn () => auth()->user()->can('viewAny', RegistrationAttendance::class)),
                    DeleteAction::make()
                        ->label('Trash')
                        ->authorize(fn (Model $record) => auth()->user()->can('delete', $record)),
                    RestoreAction::make()
                        ->color(Color::Green)
                        ->authorize(fn (Model $record) => auth()->user()->can('delete', $record)),
                    ForceDeleteAction::make()
                        ->label('Delete')
                        ->authorize(fn (Model $record) => auth()->user()->can('delete', $record)),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->authorize('Registration:delete'),
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
            'attendance' => Pages\ParticipantAttendance::route('/{record}/attendance'),
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
