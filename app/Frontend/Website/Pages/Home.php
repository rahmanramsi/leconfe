<?php

namespace App\Frontend\Website\Pages;

use App\Models\Conference;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Route;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class Home extends Page
{
    use WithPagination, WithoutUrlPagination;

    protected static string $view = 'frontend.website.pages.home';

    public array $scope;

    public array $state;

    public array $topic;

    public array $coordinator; 

    public function getTitle(): string|Htmlable
    {
        return __('general.home');
    }

    public function rules(): array
    {
        return [];
    }

    public function search(): void
    {
        dd($this->scope, $this->state, $this->topic, $this->coordinator);
    } 

    protected function getViewData(): array
    {
        $conferences = Conference::query()
            ->with([
                'media',
                'meta',
                'currentScheduledConference' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
            ])
            ->get();

        return [
            'conferences' => $conferences,
        ];
    }

    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();
        Route::get('/', static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }
}
