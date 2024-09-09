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

    public array $filter = [
        'search' => [
            'value' => '',
        ],
        'scope' => [
            'value' => '',
        ],
        'state' => [
            'value' => [],
        ],
        'topic' => [
            'search' => '',
            'value' => [],
        ],
        'coordinator' => [
            'search' => '',
            'value' => [],
        ],
    ];

    public function getTitle(): string | Htmlable
    {
        return __('general.home');
    }

    public function resetFilter(string $filterName): void
    {
        if(is_string($this->filter[$filterName]['value'])) {

            $this->filter[$filterName]['value'] = '';

        } else if(is_array($this->filter[$filterName]['value'])) {

            $this->filter[$filterName]['value'] = [];

        }
    }

    public function resetFilters(): void
    {
        $this->filter['scope']['value'] = '';
        $this->filter['state']['value'] = [];
        $this->filter['topic']['value'] = [];
        $this->filter['coordinator']['value'] = [];
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

        if(($search = $this->filter['search']['value']) > 0) {
            $conferences
                ->where('name', 'LIKE', "%{$search}%")
                ->orWhere('path', 'LIKE', "%{$search}%");
        }

        $filteredConference = $conferences->get();

        if($scope = $this->filter['scope']['value']) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) use ($scope) {
                if(Str::lower($conference->getMeta('scope')) === $scope) {
                    return true;
                }
            });
        }

        if($states = $this->filter['state']['value']) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) use ($states) {
                foreach($states as $state) {
                    if(Str::lower($state) === self::STATE_CURRENT && $conference->scheduledConferences->where('state', ScheduledConferenceState::Current)->whereNull('deleted_at')->isEmpty()) {
                        return false;
                    } else if(Str::lower($state) === self::STATE_INCOMING && $conference->scheduledConferences->where('state', ScheduledConferenceState::Published)->whereNull('deleted_at')->isEmpty()) {
                        return false;
                    } else if(Str::lower($state) === self::STATE_ARCHIVED && $conference->scheduledConferences->where('state', ScheduledConferenceState::Archived)->whereNull('deleted_at')->isEmpty()) {
                        return false;
                    }
                }

                return true;
            });
        }

        if(!empty($topics = $this->filter['topic']['value'])) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) use ($topics) {
                foreach($topics as $topic) {
                    if(!in_array($topic, $conference->topics->pluck('name')->toArray())) {
                        return false;
                    }
                }

                return true;
            });
        }

        if(!empty($coordinators = $this->filter['coordinator']['value'])) {
            $filteredConference = $filteredConference->filter(function (Conference $conference) use ($coordinators) {
                $coordinatorList = $conference->scheduledConferences->load(['meta'])->mapWithKeys(function ($scheduledConference) {
                    return [$scheduledConference->getKey() => $scheduledConference->getMeta('coordinator')];
                })->toArray();

                foreach($coordinators as $coordinator) {
                    if(!in_array($coordinator, $coordinatorList)) {
                        return false;
                    }
                }

                return true;
            });
        }

        $topics = Topic::withoutGlobalScopes()
            ->where('name', 'LIKE', "%{$this->filter['topic']['search']}%")
            ->with(['conference'])
            ->orderBy('name', 'ASC')
            ->get();

        $contributorScheduleConferences = ScheduledConference::withoutGlobalScopes()->limit(20)->get()
            ->filter(function (ScheduledConference $scheduledConference) {
                if(!$scheduledConference->getMeta('coordinator')) {
                    return false;
                }

                if($this->filter['coordinator']['search'] !== "" && !Str::contains($scheduledConference->getMeta('coordinator'), $this->filter['coordinator']['search'])) {
                    return false;
                }

                return true;
            });

        return [
            'topics' => $topics,
            'conferences' => $filteredConference,
            'contributorScheduleConferences' => $contributorScheduleConferences,
            // Selected Filter Data
            'scopeSelected' => $this->filter['scope']['value'],
            'stateSelected' => $this->filter['state']['value'],
            'topicSelected' => $this->filter['topic']['value'],
            'coordinatorSelected' => $this->filter['coordinator']['value'],
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
