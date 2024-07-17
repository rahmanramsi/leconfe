<?php

namespace App\Actions\SubmissionFiles;

use App\Models\SubmissionFileType;
use Lorisleiva\Actions\Concerns\AsAction;

class FilesTypePopulateAction
{
    use AsAction;

    public function handle()
    {
        foreach ([
            'Research Instrument',
            'Research Material',
            'Research Result',
            'Transcripts',
            'Data Analysis',
            'Data Set',
            'Source Text',
            'Other',
        ] as $type) {
            SubmissionFileType::firstOrCreate([
                'name' => $type,
            ]);
        }
    }
}
