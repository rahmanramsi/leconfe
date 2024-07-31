<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use Closure;
use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Get;
use App\Facades\Setting;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Registration;
use Filament\Facades\Filament;
use App\Models\RegistrationType;
use Illuminate\Support\HtmlString;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\RegistrationStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Panel\ScheduledConference\Resources\RegistrantResource;

class EnrollUser extends ListRecords
{
    protected static string $resource = RegistrantResource::class;

    protected static ?string $title = 'Enroll User';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        if ($user->can('Registrant:enroll')) {
            return true;
        }
        return false;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        return [
            $resource::getUrl() => $resource::getBreadcrumb(),
            'List',
            'Enroll User',
        ];
    }

    public static function getRegistrationTypeOptions(): array
    {
        $registrationTypeOptions = [];
        $registrationTypes = RegistrationType::where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())->get();
        foreach($registrationTypes as $registrationType) {
            if (!$registrationType->active) continue;

            $name = $registrationType->type;
            $quotaCurrent = $registrationType->quota;
            $quotaLeft = $registrationType->getPaidParticipantCount();
            $expired = $registrationType->isExpired() ? 'Expired' : 'Valid';

            $registrationTypeOptions[$registrationType->id] = "$name [Quota: $quotaLeft/$quotaCurrent] [$expired]";
        }
        return $registrationTypeOptions;
    }

    public static function enrollForm(Model $record)
    {
        $fullName = $record->full_name;
        $email = $record->email;
        $affiliation = $record->getMeta('affiliation');
        return [
            Fieldset::make('User details')
                ->schema([
                    Placeholder::make('user')
                        ->label('')
                        ->content(new HtmlString(<<<HTML
                            <table class='w-full'>
                                <tr>
                                    <td>
                                        <strong>Name</strong>
                                    </td>
                                    <td class='pl-5'>:</td>
                                    <td>{$fullName}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Email</strong>
                                    </td>
                                    <td class='pl-5'>:</td>
                                    <td>{$email}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Affiliation</strong>
                                    </td>
                                    <td class='pl-5'>:</td>
                                    <td>{$affiliation}</td>
                                </tr>
                            </table>
                        HTML))
                ]),
            Select::make('registration_type_id')
                ->label('Type')
                ->options(static::getRegistrationTypeOptions())
                ->searchable()
                ->required()
                ->rules([
                    fn (): Closure => function (string $attribute, $value, Closure $fail) {
                        $registrationType = RegistrationType::findOrFail($value);
                        if ($registrationType->getQuotaLeft() <= 0) {
                            $fail($registrationType->type . ' quota has ran out!');
                        }
                        if (!$registrationType->active) {
                            $fail($registrationType->type . ' not active!');
                        }
                    },
                ]),
            Fieldset::make('Payment')
                ->schema([
                    Select::make('registrationPayment.state')
                        ->options(
                            Arr::except(RegistrationStatus::array(), RegistrationStatus::Trashed->value)
                        )
                        ->default(RegistrationStatus::Unpaid->value)
                        ->native(false)
                        ->required()
                        ->live(),
                    DatePicker::make('registrationPayment.paid_at')
                        ->label('Paid Date')
                        ->placeholder('Select registration paid date..')
                        ->prefixIcon('heroicon-m-calendar')
                        ->formatStateUsing(fn () => now())
                        ->visible(fn (Get $get) => $get('registrationPayment.state') === RegistrationStatus::Paid->value)
                        ->required()
                ])
                ->columns(1),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereDoesntHave('registration', function (Builder $query) {
                        $query->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId());
                    })
            )
            ->columns([
                Split::make([
                    SpatieMediaLibraryImageColumn::make('profile')
                        ->grow(false)
                        ->collection('profile')
                        ->conversion('avatar')
                        ->width(50)
                        ->height(50)
                        ->defaultImageUrl(function (User $record): string {
                            $name = Str::of(Filament::getUserName($record))
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
                    Stack::make([
                        TextColumn::make('full_name')
                            ->weight(FontWeight::Medium)
                            ->searchable(
                                query: fn ($query, $search) => $query
                                    ->where('given_name', 'LIKE', "%{$search}%")
                                    ->orWhere('family_name', 'LIKE', "%{$search}%")
                            )
                            ->sortable(
                                query: fn ($query, $direction) => $query
                                    ->orderBy('given_name', $direction)
                                    ->orderBy('family_name', $direction)
                            ),
                        TextColumn::make('email')
                            ->wrap()
                            ->color('gray')
                            ->searchable()
                            ->size('sm')
                            ->sortable()
                            ->icon('heroicon-m-envelope'),
                        TextColumn::make('affiliation')
                            ->size('sm')
                            ->wrap()
                            ->color('gray')
                            ->icon('heroicon-s-building-library')
                            ->getStateUsing(fn (User $record) => $record->getMeta('affiliation')),
                    ]),
                ])
            ])
            ->actions([
                CreateAction::make('enroll')
                    ->label('Enroll')
                    ->modelLabel('Enroll User')
                    ->icon('heroicon-m-circle-stack')
                    ->color('gray')
                    ->button()
                    ->model(Registration::class)
                    ->form(fn (?Model $record) => static::enrollForm($record))
                    ->createAnother(false)
                    ->modalSubmitActionLabel('Enroll')
                    ->mutateFormDataUsing(function (Model $record, $data) { // record are user model
                        $registrationType = RegistrationType::where('id', $data['registration_type_id'])->first();
                        if($registrationType) {
                            $data['user_id'] = $record->id;
                            return $data;
                        }
                    })
                    ->after(function (Model $record, $data) { // record are registration model
                        $registrationType = RegistrationType::where('id', $record->registration_type_id)->first();
                        $record->registrationPayment()->create([
                            'name' => $registrationType->type,
                            'description' => $registrationType->getMeta('description'),
                            'cost' => $registrationType->cost,
                            'currency' => $registrationType->currency,
                            'state' => $data['registrationPayment']['state'],
                            'paid_at' => $data['registrationPayment']['state'] === RegistrationStatus::Paid->value ? $data['registrationPayment']['paid_at'] : null,
                        ]);
                    })
            ])
            ->extremePaginationLinks();
    }
}
