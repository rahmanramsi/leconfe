<?php

namespace App\Panel\Administration\Resources;

use App\Actions\StaticPages\StaticPageUpdateAction;
use App\Models\StaticPage;
use App\Panel\Administration\Resources\StaticPageResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use App\Forms\Components\TinyEditor;
use App\Tables\Columns\IndexColumn;
use Filament\Tables\Actions\Action;

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
                            ->where('conference_id', app()->getCurrentConference()?->getKey() ?? 0)
                            ->where('scheduled_conference_id', app()->getCurrentScheduledConference()?->getKey() ?? 0);
                    }),
                TinyEditor::make('meta.content')
                    ->label('Content')
                    ->minHeight(400)
                    ->columnSpanFull()
                    ->plugins('advlist autoresize codesample directionality emoticons fullscreen hr image imagetools link lists media table toc wordcount code')
                    ->toolbar('undo redo removeformat | formatselect fontsizeselect | bold italic | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist | forecolor backcolor | blockquote table hr | image link code')
                    ->helperText('The complete page content.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IndexColumn::make('no')
                    ->label('No.'),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->color('primary'),
            ])
            ->actions([
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => $record->getUrl())
                    ->openUrlInNewTab(),
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
