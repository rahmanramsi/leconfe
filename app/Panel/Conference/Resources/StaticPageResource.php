<?php

namespace App\Panel\Conference\Resources;

use App\Actions\StaticPages\StaticPageUpdateAction;
use App\Models\StaticPage;
use App\Panel\Conference\Resources\StaticPageResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class StaticPageResource extends Resource
{
    protected static ?string $model = StaticPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Settings';

    public static function getEloquentQuery(): Builder
    {
        $query = static::getModel()::query();

        if(!app()->getCurrentScheduledConferenceId()){
            $query->where('scheduled_conference_id', 0);
        }

        return $query;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->alphaDash()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                        return $rule
                            ->where('conference_id', app()->getCurrentConference()->getKey())
                            ->where('scheduled_conference_id', app()->getCurrentScheduledConference()?->getKey() ?? 0);
                    }),
                TinyEditor::make('meta.content')
                    ->label('Content')
                    ->minHeight(400)
                    ->columnSpanFull()
                    ->helperText('The complete page content.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->color('primary')
                    ->url(fn (StaticPage $staticPage) => $staticPage->getUrl())
                    ->openUrlInNewTab()
            ])
            ->actions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (StaticPage $record, array $data) {
                        $data['meta'] = $record->getAllMeta()->toArray();

                        return $data;
                    })
                    ->using(fn(StaticPage $record, array $data) => StaticPageUpdateAction::run($record, $data)),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaticPages::route('/'),
        ];
    }
}
