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
        if($user->can('Registrant:enroll')) {
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
        $registration_type = RegistrationType::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->get();
        $registration_type_options = [];
        foreach($registration_type as $type)
        {
            if(!$type->active) continue;
            
            $key = $type->id;
            $is_expired = $type->isExpired();
            $registration_type_options[$key] = $type->type . ' [Quota Left: ' . $type->getPaidParticipantCount() . '/' . $type->quota . '] [' . ($is_expired ? 'Expired' : 'Valid') . ']';
        }
        return $registration_type_options;
    }

    public static function enrollForm(Model $record)
    {
        return [
            Placeholder::make('user')
                ->content(new HtmlString('
                    <ul>
                        <li>Name: <strong>'.$record->full_name.'</strong></li>
                        <li>Email: <strong>'.$record->email.'</strong></li>
                        <li>Affiliation: <strong>'.$record->getMeta('affiliation').'</strong></li>
                    </ul>
                ')),
            Select::make('registration_type_id')
                ->label('Type')
                ->options(static::getRegistrationTypeOptions())
                ->searchable()
                ->required()
                ->rules([
                    fn (): Closure => function (string $attribute, $value, Closure $fail) {
                        $registration_type = RegistrationType::findOrFail($value);
                        if($registration_type->getQuotaLeft() <= 0)
                            $fail($registration_type->type . ' quota has ran out!');
                        if(!$registration_type->active)
                            $fail($registration_type->type . ' not active!');
                    },
                ]),
            Fieldset::make('Payment')
                ->schema([
                    Select::make('state')
                        ->options(
                            Arr::except(RegistrationStatus::array(), RegistrationStatus::Trashed->value)
                        )
                        ->default(RegistrationStatus::Unpaid->value)
                        ->native(false)
                        ->required()
                        ->live(),
                    DatePicker::make('paid_at')
                        ->label('Paid Date')
                        ->placeholder('Select registration paid date..')
                        ->prefixIcon('heroicon-m-calendar')
                        ->formatStateUsing(fn () => now())
                        ->visible(fn (Get $get) => $get('state') === RegistrationStatus::Paid->value)
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
                        $query->whereScheduledConferenceId(app()->getCurrentScheduledConferenceId());
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
                            // ->color(Color::hex('#A6CE39'))
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
                    ->form(fn (Model $record) => static::enrollForm($record))
                    ->createAnother(false)
                    ->modalSubmitActionLabel('Enroll')
                    ->mutateFormDataUsing(function (Model $record, $data) {
                        $registration_type = RegistrationType::where('id', $data['registration_type_id'])->first();
                        
                        if(!$registration_type) return;

                        $data['user_id'] = $record->id;
                        $data['name'] = $registration_type->type;
                        $data['cost'] = $registration_type->cost;
                        $data['currency'] = $registration_type->currency;
    
                        return $data;
                    })
            ])
            ->extremePaginationLinks();
    }
}
