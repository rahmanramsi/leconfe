<?php

namespace App\Frontend\Website\Pages;

use App\Models\Conference;
use App\Models\Topic;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class Home extends Page
{
    use WithPagination, WithoutUrlPagination;

    protected static string $view = 'frontend.website.pages.home';

    public string $search = '';

    public array $scope = [];

    public array $state = [];

    public array $topic = [];

    public array $coordinator = [];

    public function getTitle(): string|Htmlable
    {
        return __('general.home');
    }

    protected function getViewData(): array
    {
        $topics = Topic::withoutGlobalScopes()->with(['conference'])->get();

        // conferences
        $conferences = Conference::query()
            ->with([
                'media',
                'meta',
                'topics' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
                'currentScheduledConference' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
                'activeScheduledConference' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
            ]);

        if(strlen($this->search) > 0) {
            $conferences
                ->where('name', 'LIKE', "%{$this->search}%")
                ->orWhere('path', 'LIKE', "%{$this->search}%");
        }

        $filteredConference = $conferences->get()
            ->filter(function (Conference $conference) {
                if (count($this->scope) <= 0 &&
                    count($this->state) <= 0 &&
                    count($this->topic) <= 0 &&
                    count($this->coordinator) <= 0) {
                    return true;
                }

                // scope
                if (count($this->scope) > 0) {
                    if(in_array($conference->getMeta('scope'), $this->scope)) {
                        return true;
                    }
                }

                // state
                if (in_array('active', $this->state) && !$conference->activeScheduledConference->isEmpty()) {
                    return true;
                }

                if (in_array('over', $this->state) && $conference->activeScheduledConference->isEmpty()) {
                    return true;
                }
                
                // topics
                if (count($this->topic) > 0) {
                    foreach($conference->topics->pluck('id') as $id) {
                        if(in_array($id, $this->topic)) {
                            return true;
                        }
                    }
                }

                return false;
            });

        return [
            'conferences' => $filteredConference,
            'topics' => $topics,
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
