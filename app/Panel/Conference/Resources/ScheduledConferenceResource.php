<?php

namespace App\Panel\Conference\Resources;

use App\Actions\ScheduledConferences\ScheduledConferenceUpdateAction;
use App\Facades\Setting;
use App\Models\Enums\ScheduledConferenceState;
use App\Models\Enums\ScheduledConferenceType;
use App\Models\ScheduledConference;
use App\Panel\Conference\Resources\ScheduledConferenceResource\Pages;
use App\Tables\Columns\IndexColumn;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ScheduledConferenceResource extends Resource
{
    protected static ?string $model = ScheduledConference::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->autofocus()
                    ->autocomplete()
                    ->required()
                    ->placeholder('Enter the title of the serie'),
                TextInput::make('path')
                    ->prefix(fn () => route('livewirePageGroup.conference.pages.home', ['conference' => app()->getCurrentConference()->path]) . '/scheduled/')
                    ->label('Path')
                    ->rule('alpha_dash')
                    ->required(),
                Grid::make()
                    ->schema([
                        DatePicker::make('date_start')
                            ->label('Start Date')
                            ->placeholder('Enter the start date of the serie')
                            ->requiredWith('date_end'),
                        DatePicker::make('date_end')
                            ->label('End Date')
                            ->afterOrEqual('date_start')
                            ->requiredWith('date_start')
                            ->placeholder('Enter the end date of the serie'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (ScheduledConference $record) => route('filament.scheduledConference.pages.dashboard', ['serie' => $record]))
            ->modifyQueryUsing(fn (Builder $query) => $query->latest())
            ->columns([
                IndexColumn::make('no'),
                TextColumn::make('title')
                    ->searchable()
                    ->description(fn (ScheduledConference $record) => $record->current ? 'Current' : null)
                    ->sortable()
                    ->wrap()
                    ->wrapHeader(),
                TextColumn::make('date_start')
                    ->date(Setting::get('format_date')),
                TextColumn::make('date_end')
                    ->date(Setting::get('format_date')),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('publish')
                        ->label('Publish')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-arrow-up-on-square')
                        ->color('primary')
                        ->form([
                            Checkbox::make('set_as_current')
                                ->label('Set as current serie'),
                        ])
                        ->hidden(fn (ScheduledConference $record) => $record->isArchived() || $record->isCurrent() || $record->isPublished() || $record->trashed())
                        ->action(function (ScheduledConference $record, array $data, Tables\Actions\Action $action) {
                            $data['state'] = $data['set_as_current'] ? ScheduledConferenceState::Current : ScheduledConferenceState::Published;

                            ScheduledConferenceUpdateAction::run($record, $data);

                            return $action->success();
                        }),
                    Tables\Actions\Action::make('set_as_current')
                        ->label('Set As Current')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->hidden(fn (ScheduledConference $record) => $record->isCurrent() || $record->isDraft() || $record->trashed())
                        ->action(fn (ScheduledConference $record, Tables\Actions\Action $action) => $record->update(['state' => ScheduledConferenceState::Current]) && $action->success())
                        ->successNotificationTitle(fn (ScheduledConference $scheduledConference) => $scheduledConference->title . ' is set as current'),    
                    Tables\Actions\Action::make('move_to_archive')
                        ->label('Move To Archive')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->color('warning')
                        ->hidden(fn (ScheduledConference $record) => $record->isArchived() || $record->isDraft() || $record->trashed())
                        ->action(fn (ScheduledConference $record, Tables\Actions\Action $action) => $record->update(['state' => ScheduledConferenceState::Archived]) && $action->success())
                        ->successNotificationTitle(fn (ScheduledConference $scheduledConference) => $scheduledConference->title . ' is moved to archive'),
                    Tables\Actions\EditAction::make()
                        ->modalWidth(MaxWidth::ExtraLarge)
                        ->hidden(fn (ScheduledConference $record) => $record->isArchived() || $record->trashed())
                        ->mutateRecordDataUsing(function (ScheduledConference $record, array $data) {
                            $data['meta'] = $record->getAllMeta()->toArray();

                            return $data;
                        })
                        ->using(fn (ScheduledConference $record, array $data) => ScheduledConferenceUpdateAction::run($record, $data)),
                    Tables\Actions\DeleteAction::make()
                        ->label('Move To Trash')
                        ->modalHeading('Move To Trash')
                        ->hidden(fn (ScheduledConference $record) => $record->isCurrent() || $record->trashed())
                        ->successNotificationTitle('Serie moved to trash'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Delete Permanently')
                        ->hidden(fn (ScheduledConference $record) => ! $record->trashed())
                        ->successNotificationTitle('Serie deleted permanently'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageScheduledConferences::route('/'),
        ];
    }
}
