<?php

namespace App\Actions\ScheduledConferences;

use App\Models\ScheduledConference;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ScheduledConferenceUpdateAction
{
    use AsAction;

    public function handle(ScheduledConference $scheduledConference, array $data): ScheduledConference
    {
        try {
            DB::beginTransaction();

            $scheduledConference->update($data);

            if (data_get($data, 'meta')) {
                $scheduledConference->setManyMeta(data_get($data, 'meta'));
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return $scheduledConference;
    }
}
