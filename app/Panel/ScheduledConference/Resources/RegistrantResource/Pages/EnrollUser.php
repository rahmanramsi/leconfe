<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use Closure;
use App\Models\User;
use Filament\Forms\Get;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Registration;
use Filament\Facades\Filament;
use App\Models\RegistrationType;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Models\Enums\RegistrationPaymentState;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use Illuminate\Contracts\Support\Htmlable;

class EnrollUser extends ListRecords
{
    protected static string $resource = RegistrantResource::class;

    protected static ?string $title = 'Enroll Users';

    public function getTitle() : string|Htmlable
    {
        return __('general.enroll_users');
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        if ($user->can('Registration:enroll')) {
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
        $registrationTypes = RegistrationType::get();
        foreach ($registrationTypes as $registrationType) {
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
            Fieldset::make(__('general.user_details'))
                ->schema([
                    Placeholder::make('user')
                        ->label('')
                        ->content(new HtmlString(
                            "<table class='w-full'>
                                <tr>
                                    <td>
                                        <strong>" . __('general.name') . "</strong>
                                    </td>
                                    <td class='pl-5'>:</td>
                                    <td>{$fullName}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>" . __('general.email') . "</strong>
                                    </td>
                                    <td class='pl-5'>:</td>
                                    <td>{$email}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>" . __('general.affiliation') . "</strong>
                                    </td>
                                    <td class='pl-5'>:</td>
                                    <td>{$affiliation}</td>
                                </tr>
                            </table>"
                        ))
                ]),
            Select::make('registration_type_id')
                ->label(__('general.type'))
                ->options(static::getRegistrationTypeOptions())
                ->searchable()
                ->required()
                ->rules([
                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                        $registrationType = RegistrationType::findOrFail($value);
                        if ($registrationType->getQuotaLeft() <= 0) {
                            $fail($registrationType->type . ' quota has ran out!');
                        }
                        if (!$registrationType->active) {
                            $fail($registrationType->type . ' not active!');
                        }
                    },
                ]),
            Fieldset::make(__('general.payment'))
                ->schema([
                    Select::make('registrationPayment.state')
                        ->label(__('general.state'))
                        ->options(
                            RegistrationPaymentState::array()
                        )
                        ->default(RegistrationPaymentState::Unpaid->value)
                        ->native(false)
                        ->required()
                        ->live(),
                    DatePicker::make('registrationPayment.paid_at')
                        ->label(__('general.paid_Date'))
                        ->placeholder(__('general.select_registration_paid_date'))
                        ->prefixIcon('heroicon-m-calendar')
                        ->formatStateUsing(fn() => now())
                        ->visible(fn(Get $get) => $get('registrationPayment.state') === RegistrationPaymentState::Paid->value)
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
                                ->map(fn(string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
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
                                query: fn($query, $search) => $query
                                    ->where('given_name', 'LIKE', "%{$search}%")
                                    ->orWhere('family_name', 'LIKE', "%{$search}%")
                            )
                            ->sortable(
                                query: fn($query, $direction) => $query
                                    ->orderBy('given_name', $direction)
                                    ->orderBy('family_name', $direction)
                            ),
                        TextColumn::make('email')
                            ->label(__('general.email'))
                            ->wrap()
                            ->color('gray')
                            ->searchable()
                            ->size('sm')
                            ->sortable()
                            ->icon('heroicon-m-envelope'),
                        TextColumn::make('affiliation')
                            ->label(__('general.affiliation'))
                            ->size('sm')
                            ->wrap()
                            ->color('gray')
                            ->icon('heroicon-s-building-library')
                            ->getStateUsing(fn(User $record) => $record->getMeta('affiliation')),
                    ]),
                ])
            ])
            ->actions([
                CreateAction::make('enroll')
                    ->label(__('general.enroll'))
                    ->modelLabel(__('general.enroll_user'))
                    ->icon('heroicon-m-circle-stack')
                    ->color('gray')
                    ->button()
                    ->model(Registration::class)
                    ->form(fn(?Model $record) => static::enrollForm($record))
                    ->createAnother(false)
                    ->modalSubmitActionLabel(__('general.enroll'))
                    ->mutateFormDataUsing(function (Model $record, $data) { // record are user model
                        $registrationType = RegistrationType::where('id', $data['registration_type_id'])->first();
                        if ($registrationType) {
                            $data['user_id'] = $record->id;
                            return $data;
                        }
                    })
                    ->after(function (Model $record, $data) { // record are registration model
                        $registrationType = RegistrationType::where('id', $record->registration_type_id)->first();
                        $record->registrationPayment()->create([
                            'name' => $registrationType->type,
                            'level' => $registrationType->level,
                            'description' => $registrationType->getMeta('description'),
                            'cost' => $registrationType->cost,
                            'currency' => $registrationType->currency,
                            'state' => $data['registrationPayment']['state'],
                            'paid_at' => $data['registrationPayment']['state'] === RegistrationPaymentState::Paid->value ? $data['registrationPayment']['paid_at'] : null,
                        ]);

                        $record->user->notify(
                            new \App\Notifications\RegistrationEnroll(
                                registration: $record,
                            )
                        );
                    })
            ])
            ->extremePaginationLinks();
    }
}
