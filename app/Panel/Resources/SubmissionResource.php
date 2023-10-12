<?php

namespace App\Panel\Resources;

use App\Models\Submission;
use App\Panel\Resources\SubmissionResource\Pages;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->getMeta('title');
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user']);
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record?->getMeta('title') ?? static::getModelLabel();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['meta']);
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        $search = strtolower($search);

        $query = static::getGlobalSearchEloquentQuery();

        $isFirst = true;
        foreach (explode(' ', $search) as $searchWord) {
            $whereHas = $isFirst ? 'whereHas' : 'orWhereHas';

            $query->{$whereHas}('meta', function (Builder $q) use ($searchWord) {
                $q->whereIn('key', static::getGloballySearchableAttributes())
                    ->where('value', 'like', "%{$searchWord}%");
            });

            $isFirst = false;
        }

        return $query
            ->limit(static::getGlobalSearchResultsLimit())
            ->get()
            ->map(function (Model $record): ?GlobalSearchResult {
                $url = static::getGlobalSearchResultUrl($record);
                if (blank($url)) {
                    return null;
                }

                return new GlobalSearchResult(
                    title: static::getGlobalSearchResultTitle($record),
                    url: $url,
                    details: static::getGlobalSearchResultDetails($record),
                    actions: static::getGlobalSearchResultActions($record),
                );
            })
            ->filter();
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Author' => $record->user->name,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (Submission $record) {
                $userAsParticipant = auth()->user()->asParticipant();
                if (!$userAsParticipant) {
                    return static::getUrl('view', [
                        'record' => $record->id,
                    ]);
                }
                $participantReviewer = $record->reviewAssignments()->where('participant_id', $userAsParticipant->getKey())->first();

                if ($participantReviewer) {
                    return static::getUrl('review', [
                        'record' => $record->id,
                    ]);
                }

                if ($participantReviewer->needConfirmation()) {
                    return static::getUrl('reviewer-request', [
                        'record' => $record->id,
                    ]);
                }

                return static::getUrl('view', [
                    'record' => $record->id,
                ]);
            })
            ->columns([
                Split::make([
                    Tables\Columns\TextColumn::make('title')
                        ->getStateUsing(fn (Submission $record) => $record->getMeta('title'))
                        ->description(function (Submission $record) {
                            return $record->user->fullName;
                        })
                        ->searchable(query: function (Builder $query, string $search): Builder {
                            return $query
                                ->whereMeta('title', 'like', "%{$search}%");
                        }),
                    Tables\Columns\TextColumn::make('status')
                        ->badge()
                        ->color('warning')
                        ->formatStateUsing(
                            fn (Submission $record) => $record->status
                        )
                ])
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Submission $record) => static::getUrl('view', [
                        'record' => $record->id,
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSubmissions::route('/'),
            'create' => Pages\CreateSubmission::route('/create'),
            'complete' => Pages\CompleteSubmission::route('/complete/{record}'),
            'view' => Pages\ViewSubmission::route('/{record}'),
            'review' => Pages\ReviewSubmissionPage::route('/{record}/review'),
            'reviewer-request' => Pages\ReviewerRequestPage::route('/{record}/reviewer-request'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
