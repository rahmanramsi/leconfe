<?php

namespace App\Frontend\Conference\Pages;

use App\Frontend\Conference\Pages\Proceedings as PagesProceedings;
use App\Models\Enums\SubmissionStatus;
use App\Models\Proceeding;
use App\Models\Track;
use Illuminate\Support\Facades\Route;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;
use Illuminate\Support\Str;

class ProceedingDetail extends Page
{
    protected static string $view = 'frontend.conference.pages.proceeding-detail';

    public Proceeding $proceeding;

    public function mount(Proceeding $proceeding)
    {
        abort_unless($this->canAccess(), 404);
    }

    public function canAccess(): bool
    {
        return $this->proceeding->isPublished();
    }

    public function canPreview(): bool
    {
        return !$this->proceeding->isPublished();
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            route(PagesProceedings::getRouteName()) => 'Proceedings',
            Str::limit($this->proceeding->seriesTitle(), 120),
        ];
    }

    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();
        Route::get("/proceedings/view/{proceeding}", static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }

    public function getViewData(): array
    {
        $tracks = Track::query()
            ->with(['submissions' => fn ($query) => $query
                ->where('proceeding_id', $this->proceeding->id)
                ->where('status', SubmissionStatus::Published)
                ->with(['authors', 'doi', 'galleys.file.media', 'meta'])])
            ->whereHas('submissions', fn ($query) => $query
                ->where('proceeding_id', $this->proceeding->id)
                ->where('status', SubmissionStatus::Published))
            ->get();

        return [
            'proceeding' => $this->proceeding,
            'tracks' => $tracks,
        ];
    }
}
