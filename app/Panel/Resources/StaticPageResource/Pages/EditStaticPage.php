<?php

namespace App\Panel\Resources\StaticPageResource\Pages;

use App\Actions\StaticPages\StaticPageUpdateAction;
use App\Panel\Resources\StaticPageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStaticPage extends EditRecord
{
    protected static string $resource = StaticPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('view')
                ->icon('heroicon-o-eye')
                ->label('View as page')
                ->color('success')
                ->url(fn ($record) => route('livewirePageGroup.website.pages.static-page', [
                    'slug' => $record->slug,
                ])),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return StaticPageUpdateAction::run($data, $record);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $userContentMeta = $this->record->getAllMeta();
        $user = $this->record->user;

        $data['author'] = $user ? "{$user->given_name} {$user->family_name}" : 'Cannot find the author';
        $data['common_tags'] = $this->record->tags()->pluck('id')->toArray();
        $data['user_content'] = $userContentMeta['user_content'];

        return $data;
    }
}
