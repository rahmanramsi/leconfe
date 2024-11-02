<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use App\Models\Enums\RegistrationPaymentState;
use App\Models\Registration;
use App\Models\RegistrationType;
use App\Models\User;
use App\Panel\ScheduledConference\Resources\RegistrantResource;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;

class EnrollUser extends ListRecords
{
    protected static string $resource = RegistrantResource::class;

    protected static ?string $title = 'Enroll Users';

    public function getTitle(): string|Htmlable
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

    public static function enrollForm(Model $record, int $level)
    {
        $fullName = $record->full_name;
        $email = $record->email;
        $affiliation = $record->getMeta('affiliation');

        return [
            Fieldset::make('user-details')
                ->columns(1)
                ->label(__('general.user_details'))
                ->schema([
                    Placeholder::make('user')
                        ->label('')
                        ->content(new HtmlString(BladeCompiler::render(<<<BLADE
                            <table class='w-full'>
                                <tr>
                                    <td>
                                        <strong>{{ __('general.name') }}</strong>
                                    </td>
                                    <td>:</td>
                                    <td><strong class="font-semibold">{$fullName}</strong></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>{{ __('general.email') }}</strong>
                                    </td>
                                    <td>:</td>
                                    <td>{$email}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>{{ __('general.affiliation') }}</strong>
                                    </td>
                                    <td>:</td>
                                    <td>{$affiliation}</td>
                                </tr>
                            </table>
                        BLADE))),
                ]),
            Fieldset::make('registration-details')
                ->columns(1)
                ->label(new HtmlString(__('general.this_registration_details', ['full_name' => $fullName])))
                ->schema([
                    Placeholder::make('registration')
                        ->label('')
                        ->content(function (Get $get) {
                            $registrationType = RegistrationType::find($get('registration_type_id'));

                            if (! $registrationType) {
                                return __('general.registration_type_have_to_selected');
                            }

                            $description = $registrationType->getMeta('description') ?? '-';
                            $cost = moneyOrFree($registrationType->cost, $registrationType->currency, true);
                            $status = $registrationType->isOpen() ? 'Open' : 'Closed';

                            return new HtmlString(BladeCompiler::render(<<<BLADE
                                <table class='w-full'>
                                    <tr>
                                        <td>
                                            <strong>{{ __('general.registration_type') }}</strong>
                                        </td>
                                        <td>:</td>
                                        <td><strong class="font-semibold">{$registrationType->type}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>{{ __('general.description') }}</strong>
                                        </td>
                                        <td>:</td>
                                        <td>{$description}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>{{ __('general.cost') }}</strong>
                                        </td>
                                        <td>:</td>
                                        <td>{$cost}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>{{ __('general.quota') }}</strong>
                                        </td>
                                        <td>:</td>
                                        <td>{$registrationType->getPaidParticipantCount()}/{$registrationType->quota}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>{{ __('general.status') }}</strong>
                                        </td>
                                        <td>:</td>
                                        <td>{$status}</td>
                                    </tr>
                                </table>
                            BLADE));
                        }),
                ]),
            Select::make('registration_type_id')
                ->label(__('general.registration_type'))
                ->options(
                    RegistrationType::where('level', $level)
                        ->get()
                        ->pluck('type', 'id')
                        ->toArray()
                )
                ->searchable()
                ->required()
                ->rules([
                    fn (): Closure => function (string $attribute, $value, Closure $fail) {
                        $registrationType = RegistrationType::findOrFail($value);
                        if ($registrationType->getQuotaLeft() <= 0) {
                            $fail($registrationType->type.' quota has ran out!');
                        }
                        if (! $registrationType->active) {
                            $fail($registrationType->type.' not active!');
                        }
                    },
                ])
                ->live(),
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
                        ->label(__('general.paid_date'))
                        ->placeholder(__('general.select_registration_paid_date'))
                        ->prefixIcon('heroicon-m-calendar')
                        ->formatStateUsing(fn () => now())
                        ->visible(fn (Get $get) => $get('registrationPayment.state') === RegistrationPaymentState::Paid->value)
                        ->required(),
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

                            return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=FFFFFF&background=111827&font-size=0.33';
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
                            ->getStateUsing(fn (User $record) => $record->getMeta('affiliation')),
                    ]),
                ]),
            ])
            ->actions([
                CreateAction::make('enroll')
                    ->label(__('general.enroll'))
                    ->modelLabel(__('general.enroll_user'))
                    ->icon('heroicon-m-circle-stack')
                    ->color('gray')
                    ->button()
                    ->model(Registration::class)
                    ->form(fn (?Model $record) => static::enrollForm($record, RegistrationType::LEVEL_PARTICIPANT))
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

                        try {
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
                        } catch (\Throwable $th) {
                            throw $th;
                        }
                    }),
            ])
            ->extremePaginationLinks();
    }
}
