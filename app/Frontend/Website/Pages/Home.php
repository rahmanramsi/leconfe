<?php

namespace App\Frontend\Website\Pages;

use App\Models\Conference;
use App\Models\ScheduledConference;
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

    public ?string $scope = null;

    public ?string $state = null;

    public array $topic = [];

    public array $coordinator = [];

    public function getTitle(): string|Htmlable
    {
        return __('general.home');
    }

    public function clearFilter(): void
    {
        $this->search = '';
        $this->scope = null;
        $this->state = null;
        $this->topic = [];
        $this->coordinator = [];
    }

    public function clearScope(): void
    {
        $this->scope = null;
    }

    public function clearState(): void
    {
        $this->state = null;
    }

    protected function getViewData(): array
    {
        // conferences
        $conferences = Conference::query()
            ->with([
                'media',
                'meta',
                'topics' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
                'currentScheduledConference' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
                'activeScheduledConference' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
                'scheduledConferences' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
            ]);

        if(strlen($this->search) > 0) {
            $conferences
                ->where('name', 'LIKE', "%{$this->search}%")
                ->orWhere('path', 'LIKE', "%{$this->search}%");
        }

        // TODO: change to select
        // TODO: fix filtering logic, instead of showing all from selected checkbox, use select to filter, because data are unique per conference
        $filteredConference = $conferences->get();

        if($this->scope) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) {
                if($conference->getMeta('scope') === $this->scope) {
                    return true;
                }
            });
        }

        if($this->state) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) {
                if($this->state === 'active' && !$conference->activeScheduledConference->isEmpty()) {
                    return true;
                } else if($this->state === 'over' && $conference->activeScheduledConference->isEmpty()) {
                    return true;
                }
            });
        }

        if(count($this->topic) > 0) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) {
                foreach($this->topic as $topic) {
                    if(!in_array((int) $topic, $conference->topics->pluck('id')->toArray())) {
                        return false;
                    }
                }

                return true;
            });
        }

        if(count($this->coordinator) > 0) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) {
                foreach($this->coordinator as $coordinator) {
                    if(!in_array($coordinator, $conference->scheduledConferences->pluck('id')->toArray())) {
                        return false;
                    }
                }

                return true;
            });
        }

        $topics = Topic::withoutGlobalScopes()->with(['conference'])->orderBy('name', 'ASC')->get();

        $scheduledConferencesWithCoordinators = ScheduledConference::withoutGlobalScopes()->get()
            ->filter(function (ScheduledConference $scheduledConference) {
                if(!$scheduledConference->getMeta('coordinator')) {
                    return false;
                }

                return true;
            });

        return [
            'scheduledConferencesWithCoordinators' => $scheduledConferencesWithCoordinators,
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
