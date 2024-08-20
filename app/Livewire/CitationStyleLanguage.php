<?php

namespace App\Livewire;

use App\Facades\Hook;
use App\Models\Submission;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\StyleSheet;

class CitationStyleLanguage extends Component
{
    #[Locked] 
    public Submission $submission;

    #[Locked] 
    public string $citationStyle = 'bibtex';

    public function mount() {}

    public function render()
    {
        return view('livewire.citation-style-language', [
            ...$this->getCitationData(),
        ]);
    }

    public function getCitationData()
    {
        $paper = $this->submission;

        $citationData = new \stdClass();
        $citationData->type = 'paper-conference';
        $citationData->id   = $paper->getKey();
        $citationData->title = $paper->getMeta('title');
        $citationData->{'container-title'} = $paper->conference->name;
        $citationData->volume = $paper->proceeding->volume;
        $citationData->issue  = $paper->proceeding->number;
        if($citationData->getMeta('article_pages')){
            $citationData->page = $paper->getMeta('article_pages');
        }

        $citationData->section = $paper->track->title;
        $citationData->keywords = $paper->getMeta('keywords') ?? [];
        $citationData->abstract = strip_tags($paper->getMeta('abstract'));
        $citationData->author = $paper->authors->map(function ($author) {
            $currentAuthor = new \stdClass();
            $currentAuthor->family = $author->family_name;
            $currentAuthor->given = $author->given_name;

            return $currentAuthor;
        })->toArray();

        $citationData->URL = $paper->getUrl();
        
        if($paper->doi?->doi){
            $citationData->DOI = $paper->doi->doi;
        }

        $citationData->{'container-title-short'} = $paper->conference->path;

        $accessed = new \stdClass();
        $accessed->raw = date('Y-m-d');
        $citationData->accessed = $accessed;

        $issued = new \stdClass();
        $issued->raw = $paper->published_at->format('Y-m-d');
        $citationData->issued = $issued;

        
        //Clickable URL and DOI including affixes
        $additionalMarkup = [
            'DOI' => [
                'function' => function ($item, $renderedValue) {
                    return '<a href="https://doi.org/' . $item->DOI . '">' . $renderedValue . '</a>';
                },
                'affixes' => true
            ],
            'URL' => [
                'function' => function ($item, $renderedValue) {
                    return '<a href="' . $item->URL . '">' . $renderedValue . '</a>';
                },
                'affixes' => true
            ],
        ];

        $style = StyleSheet::loadStyleSheet($this->citationStyle);
        $citeProc = new CiteProc($style, markupExtension: $additionalMarkup);


        return [
            'cssStyles' =>  $citeProc->renderCssStyles(),
            'citationRender' => $citeProc->render([$citationData], "bibliography"),
        ];
    }

    /**
     * Get list of citation styles available
     */
    public function getCitationStyles(): array
    {
        $defaults = [
            [
                'id' => 'acm-sig-proceedings',
                'title' => 'ACM',
            ],
            [
                'id' => 'acs-nano',
                'title' => 'ACS',
            ],
            [
                'id' => 'apa',
                'title' => 'APA',
            ],
            [
                'id' => 'associacao-brasileira-de-normas-tecnicas',
                'title' => 'ABNT',
            ],
            [
                'id' => 'chicago-author-date',
                'title' => 'Chicago',
            ],
            [
                'id' => 'harvard-cite-them-right',
                'title' => 'Harvard',
            ],
            [
                'id' => 'ieee',
                'title' => 'IEEE',
            ],
            [
                'id' => 'modern-language-association',
                'title' => 'MLA',
            ],
            [
                'id' => 'turabian-fullnote-bibliography',
                'title' => 'Turabian',
            ],
            [
                'id' => 'vancouver',
                'title' => 'Vancouver',
            ],
            [
                'id' => 'ama',
                'title' => 'AMA',
            ],
        ];

        Hook::call('citationstylelanguage::defaultStyles', $defaults);

        return $defaults;
    }

    /**
     * Load a CSL style and return the contents as a string
     */
    public function loadStyle(array $styleConfig): false|string
    {
        $path = empty($styleConfig['useCsl'])
            ? base_path('data/citation-styles/') . $styleConfig['id'] . '.csl'
            : $styleConfig['useCsl'];
        return file_get_contents($path);
    }
}
