TY  - CPAPER
@foreach ($citationData->author as $author)
AU  - {{ $author->family }}, {{ $author->given }}
@endforeach
TI  - {{ $citationData->title }}
PY  - {{ Carbon\Carbon::parse($citationData->issued->raw)->format('Y/m/d') }}
Y2  - {{ Carbon\Carbon::parse($citationData->accessed->raw)->format('Y/m/d') }}
JF  - {{ $citationData->{'container-title'} }}
JA  - {{ $citationData->{'container-title-short'} }}
VL  - {{ $citationData->volume }}
IS  - {{ $citationData->issue }}
SE  - {{ $citationData->section }}
@foreach($citationData->languages as $language)
LA  - {{ $language }}
@endforeach
@foreach ($citationData->keywords as $keyword)
KW  - {{ $keyword }}
@endforeach
@if ($citationData->DOI)
DO  - {{ $citationData->DOI }}
UR  - https://doi.org/{{ $citationData->DOI }}
@else
UR  - {{ $citationData->URL }}
@endif
@if ($citationData->page)
SP  - {{ $citationData->page }}
@endif
@if ($citationData->abstract)
AB  - {{ str_replace(["\r\n", "\n"], "", $citationData->abstract) }}
@endif
ER  -