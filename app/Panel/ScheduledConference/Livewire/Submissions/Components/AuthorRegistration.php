<?php

namespace App\Panel\ScheduledConference\Livewire\Submissions\Components;

use Filament\Tables\Table;
use App\Models\PaymentManual;
use App\Models\RegistrationType;
use Illuminate\Support\HtmlString;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;
use Filament\Infolists\Components\TextEntry;
use Illuminate\View\Compilers\BladeCompiler;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Contracts\Database\Eloquent\Builder;

class AuthorRegistration extends \Livewire\Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RegistrationType::query()
                    ->where('level', RegistrationType::LEVEL_AUTHOR)
                    ->where('active', true)
            )
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('type')
                    ->label('Registration Type')
                    ->description(fn (Model $record) => $record->getMeta('description')),
                TextColumn::make('quota')
                    ->formatStateUsing(fn (Model $record) => $record->getPaidParticipantCount() . "/" . $record->quota)
                    ->badge(),
                TextColumn::make('cost')
                    ->formatStateUsing(fn (Model $record) => moneyOrFree($record->cost, $record->currency, true))
            ])
            ->actions([
                Action::make('author-registration')
                    ->label('Register')
                    ->requiresConfirmation()
                    ->modalHeading('Author Registration')
                    ->modalIcon('heroicon-m-user-plus')
                    ->modalDescription(fn (Model $record) => new HtmlString(BladeCompiler::render(<<<BLADE
                        <div class="text-sm text-left">
                            <p>Are you sure you want register as <strong>{$record->type}</strong>?</p>
                            <p>Here's your registration details:</p>
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
                                        {$record->getMeta('description')}
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
                            </table>
                        </div>
                    BLADE)))
                    ->action(function (array $data) {
                        
                    })
            ])
            ->recordAction('author-registration')
            ->paginated(false);
    }
    
    public function render()
    {
        return view('tables.table');
    }
}
