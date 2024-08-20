<section class="citation">
    @if(!empty($cssStyles))
    <style type="text/css" rel="stylesheet">
        /* {!! $cssStyles !!} */
    </style>
    @endif
    <h2 class="pb-1 mb-3 text-xl font-medium border-b border-b-slate-200">
        {{ __('general.how_to_cite') }}
    </h2>
    <div class="mt-4 content text-slate-800">
        <div id="citationOutput" class="user-content">
            {!! $citationRender !!}
        </div>
    </div>
</section>