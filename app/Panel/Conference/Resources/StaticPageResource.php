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

    protected static ?string $navigationGroup = 'Website Management';

    public static function getEloquentQuery(): Builder
    {
        $query = static::getModel()::query();

        if(!app()->getCurrentSerieId()){
            $query->where('serie_id', 0);
        }

        return $query;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('slug')
                    ->alphaDash()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                        return $rule
                            ->where('conference_id', app()->getCurrentConference()->getKey())
                            ->where('serie_id', app()->getCurrentSerie()?->getKey() ?? 0);
                    }),
                TextInput::make('title')
                    ->required(),
                TinyEditor::make('meta.content')
                    ->label('Content')
                    ->minHeight(600)
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
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                EditAction::make()
                    ->using(fn(StaticPage $record, array $data) => StaticPageUpdateAction::run($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListStaticPages::route('/'),
        ];
    }
}
