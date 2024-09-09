<?php

namespace App\Frontend\Website\Pages;

use App\Models\Topic;
use App\Models\Conference;
use App\Models\Enums\ScheduledConferenceState;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use App\Models\ScheduledConference;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Support\Htmlable;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Home extends Page
{
    use WithPagination, WithoutUrlPagination;

    protected static string $view = 'frontend.website.pages.home';

    public const STATE_CURRENT = 'current';
    public const STATE_INCOMING = 'incoming';
    public const STATE_ARCHIVED = 'archived';

    public string $search = "";
    public ?string $scope = null;
    public array $state = [];
    public array $topic = [];
    public array $coordinator = [];

    protected $listeners = [
        'changeFilter' => 'filterChanged',
    ];

    public function getTitle(): string|Htmlable
    {
        return __('general.home');
    }

    public function clearFilter(?string $specifiedFilter = null): void
    {
        $this->dispatch('clearFilter', specifiedFilter: $specifiedFilter);
    }

    public function filterChanged(array $filterData)
    {
        $this->scope = $filterData['scope'] ?? $this->scope;
        $this->state = $filterData['state'] ?? $this->state;
        $this->topic = $filterData['topic'] ?? $this->topic;
        $this->coordinator = $filterData['coordinator'] ?? $this->coordinator;
    }

    protected function getViewData(): array
    {
        $conferences = Conference::query()
            ->with([
                'media',
                'meta',
                'topics' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
                'currentScheduledConference' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
                'scheduledConferences' => fn (Builder $query) => $query->with('conference')->withoutGlobalScopes(),
            ]);

        if(strlen($this->search) > 0) {
            $conferences
                ->where('name', 'LIKE', "%{$this->search}%")
                ->orWhere('path', 'LIKE', "%{$this->search}%");
        }

        $filteredConference = $conferences->get();

        if($this->scope) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) {
                if(Str::lower($conference->getMeta('scope')) === $this->scope) {
                    return true;
                }
            });
        }

        if($this->state) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) {
                foreach($this->state as $state) {
                    switch(Str::lower($state)) {
                        case self::STATE_CURRENT:
                            return $conference->scheduledConferences
                                ->where('state', ScheduledConferenceState::Current)
                                ->isNotEmpty();
                        case self::STATE_INCOMING:
                            return $conference->scheduledConferences
                                ->where('state', ScheduledConferenceState::Published)
                                ->isNotEmpty();
                        case self::STATE_ARCHIVED:
                            return $conference->scheduledConferences
                                ->where('state', ScheduledConferenceState::Archived)
                                ->isNotEmpty();
                    }
                }
            });
        }

        if(!empty($this->topic)) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) {
                foreach($this->topic as $topic) {
                    if(!in_array($topic, $conference->topics->pluck('name')->toArray())) {
                        return false;
                    }
                }

                return true;
            });
        }

        if(!empty($this->coordinator)) {
            // super unoptimized code (i had no other idea)
            $filteredConference = $filteredConference->filter(function (Conference $conference) {
                $coordinators = $conference->scheduledConferences->load(['meta'])->mapWithKeys(function ($scheduledConference) {
                    return [$scheduledConference->getKey() => $scheduledConference->getMeta('coordinator')];
                })->toArray();

                foreach($this->coordinator as $coordinator) {
                    if(!in_array($coordinator, $coordinators)) {
                        return false;
                    }
                }

                return true;
            });
        }

        $topics = Topic::withoutGlobalScopes()
            ->with(['conference'])
            ->orderBy('name', 'ASC')
            ->get();

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
