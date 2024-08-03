<?php

namespace App\Frontend\Conference\Pages;

use App\Facades\MetaTag;
use App\Models\Submission;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Route;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;
use Illuminate\Support\Str;

class Paper extends Page
{
    protected static string $view = 'frontend.conference.pages.paper';

    public ?Submission $paper;

    public function mount($submission)
    {
        $this->paper = Submission::query()
            ->where('id', $submission)
            ->with(['media','meta', 'galleys.file.media', 'authors' => fn($query) => $query->with(['role', 'meta'])])
            ->first();

        if(!$this->paper){
            return abort(404);
        }

        if (!$this->canAccess()) {
            abort(404);
        }

        $this->addMetadata();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->paper->getMeta('title');
    }

    public function addMetadata() : void
    {
        MetaTag::add('citation_conference_title', app()->getCurrentConference()->name);
        MetaTag::add('citation_title', e($this->paper->getMeta('title')));

        $this->paper->authors->each(function ($author) {
            MetaTag::add('citation_author', $author->fullName);
            if($author->getMeta('affiliation')){
                MetaTag::add('citation_author_affiliation', $author->getMeta('affiliation'));
            }
        });

        if($this->paper->isPublished()){
            MetaTag::add('citation_publication_date', $this->paper->published_at?->format('Y/m/d'));
        }

        $proceeding = $this->paper->proceeding;
        MetaTag::add('citation_volume', $proceeding->volume);
        MetaTag::add('citation_issue', $proceeding->number);

        if($this->paper->getMeta('article_pages')){
            [$start, $end] = explode('-', $this->paper->getMeta('article_pages'));

            if($start){
                MetaTag::add('citation_firstpage', $start);
            }

            if($end){
                MetaTag::add('citation_lastpage', $end);
            }
        }

        MetaTag::add('citation_abstract_html_url', route(static::getRouteName(), ['submission' => $this->paper->getKey()]));
        
        $this->paper->galleys->each(function ($galley) {
            if($galley->isPdf()){
                MetaTag::add('citation_pdf_url', $galley->getUrl());
            }
        });

    }

    public function canAccess(): bool
    {
        if (!$this->paper->proceeding) {
            return false;
        }

        if (auth()->user()?->can('editing', $this->paper)) {
            return true;
        }

        if ($this->paper->isPublished() && $this->paper->proceeding->isPublished()) {
            return true;
        }

        return false;
    }

    public function canPreview(): bool
    {
        if (!$this->paper->proceeding?->isPublished()) {
            return true;
        }

        $isSubmissionNotPublished = !$this->paper->isPublished();

        $canUserEdit = auth()->user()?->can('editing', $this->paper);

        if ($isSubmissionNotPublished && $canUserEdit) {
            return true;
        }

        return false;
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            route(Proceedings::getRouteName()) => 'Proceedings',
            route(ProceedingDetail::getRouteName(), [$this->paper->proceeding->id]) => Str::limit(
                $this->paper->proceeding->seriesTitle(), 70
            ),
            'Article'
        ];
    }

    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();
        Route::get("/paper/view/{submission}", static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }
}
