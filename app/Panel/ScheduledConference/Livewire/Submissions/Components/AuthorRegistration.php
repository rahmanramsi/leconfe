<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components;

use App\Models\User;
use App\Facades\Setting;
use App\Models\Submission;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Registration;
use App\Models\PaymentManual;
use App\Models\RegistrationType;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use App\Notifications\NewRegistration;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;
use Filament\Infolists\Components\TextEntry;
use Illuminate\View\Compilers\BladeCompiler;
use App\Models\Enums\RegistrationPaymentState;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Panel\ScheduledConference\Resources\SubmissionResource;

class AuthorRegistration extends \Livewire\Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public Submission $submission;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                RegistrationType::query()
                    ->where('level', RegistrationType::LEVEL_AUTHOR)
                    ->where('active', true)
            )
            ->heading(new HtmlString(BladeCompiler::render(<<<BLADE
                <strong class="font-semibold">{{ __('general.author_registration') }}</strong>
                <p class="text-sm font-normal mt-1 text-gray-500">You have to register to one of these registration type below to finish your submission payment.</p>
            BLADE)))
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('type')
                    ->label(__('general.registration_type'))
                    ->description(fn (Model $record) => $record->getMeta('description')),
                TextColumn::make('quota')
                    ->formatStateUsing(fn (Model $record) => $record->getPaidParticipantCount() . "/" . $record->quota)
                    ->badge()
                    ->color(fn (Model $record) => $record->isQuotaFull() ? Color::Red : null),
                TextColumn::make('status')
                    ->getStateUsing(fn (Model $record) => $record->isOpen())
                    ->formatStateUsing(fn (string $state) => (bool) $state ? __('general.open') : __('general.closed'))
                    ->badge()
                    ->color(fn (string $state) => (bool) $state ? Color::Green : Color::Red),
                TextColumn::make('cost')
                    ->formatStateUsing(fn (Model $record) => moneyOrFree($record->cost, $record->currency, true)),
            ])
            ->actions([
                Action::make('author-registration')
                    ->label(__('general.register'))
                    ->size('xs')
                    ->successNotificationTitle(__('general.saved'))
                    ->failureNotificationTitle(__('general.failed'))
                    ->requiresConfirmation()
                    ->modalHeading(__('general.author_registration'))
                    ->modalIcon('heroicon-m-user-plus')
                    ->modalDescription(function (Model $record) {
                        $description = $record->getMeta('description') ?? '-';
                        $date = "{$record->opened_at->format(Setting::get('format_date'))} - {$record->closed_at->format(Setting::get('format_date'))}";

                        return new HtmlString(BladeCompiler::render(<<<BLADE
                            <div class="text-sm text-left">
                                <p>{!! __('general.are_you_sure_registration', ['type' => "{$record->type}"]) !!}</p>
                                <p>{{ __('general.heres_your_registration_details') }}:</p>
                                <table class="mt-3 w-full">
                                    <tr>
                                        <td class="font-semibold">{{ __('general.type') }}</td>
                                        <td>:</td>
                                        <td>
                                            {$record->type}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">{{ __('general.description') }}</td>
                                        <td>:</td>
                                        <td>
                                            {$description}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">{{ __('general.level') }}</td>
                                        <td>:</td>
                                        <td>
                                            <x-filament::badge color="info" class="!w-fit">
                                                {{
                                                    match ($record->level) {
                                                        App\Models\RegistrationType::LEVEL_PARTICIPANT => 'Participant',
                                                        App\Models\RegistrationType::LEVEL_AUTHOR => 'Author',
                                                        default => 'None',
                                                    }
                                                }}
                                            </x-filament::badge>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">{{ __('general.cost') }}</td>
                                        <td>:</td>
                                        <td>
                                            {{ moneyOrFree($record->cost, "$record->currency", true) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">{{ __('general.date') }}</td>
                                        <td>:</td>
                                        <td>
                                            {$date}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        BLADE));
                    })
                    ->action(function (Model $record, Action $action) {
                        if($this->submission->registration) {
                            return $action->sendFailureNotification();
                        }

                        if(!$record->isOpen()) {
                            return $action->sendFailureNotification();
                        }

                        $isFree = Str::lower($record->currency) === 'free';

                        try {
                            $registration = $this->submission->registration()->create([
                                'user_id' => auth()->user()->id,
                                'registration_type_id' => $record->id,
                            ]);
                    
                            $registration->registrationPayment()->create([
                                'name' => $record->type,
                                'level' => $record->level,
                                'description' => $record->getMeta('description'),
                                'cost' => $record->cost,
                                'currency' => $record->currency,
                                'state' => $isFree ? RegistrationPaymentState::Paid : RegistrationPaymentState::Unpaid,
                            ]);
                    
                            User::whereHas('roles', function ($query) {
                                $query->whereHas('permissions', function ($query) {
                                    $query->where('name', 'Registration:notified');
                                });
                            })->get()->each(function ($user) use($registration) {
                                $user->notify(
                                    new NewRegistration(
                                        registration: $registration,
                                    )
                                );
                            });
                        } catch (\Throwable $th) {
                            $action->failure();
                            throw $th;
                        }

                        $action->successRedirectUrl(
                            SubmissionResource::getUrl('view', [
                                'record' => $this->submission->getKey()
                            ])
                        );

                        return $action->success();
                    })
                    ->visible(fn (Model $record) => $record->isOpen()),
            ])
            ->recordAction('author-registration')
            ->paginated(false);
    }
    
    public function render()
    {
        return view('tables.table');
    }
}
