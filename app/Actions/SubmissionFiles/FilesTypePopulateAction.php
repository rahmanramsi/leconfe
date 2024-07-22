<?php

namespace App\Actions\SubmissionFiles;

use App\Models\ScheduledConference;
use App\Models\SubmissionFileType;
use Lorisleiva\Actions\Concerns\AsAction;

class FilesTypePopulateAction
{
    use AsAction;

    public function handle(ScheduledConference $scheduledConference)
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
        ] as $name) {
            SubmissionFileType::firstOrCreate([
                'name' => $name,
                'scheduled_conference_id' => $scheduledConference->id,
            ]);
        }
    }
}
