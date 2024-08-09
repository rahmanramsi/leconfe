<?php

namespace App\Actions\Stakeholders;

use App\Models\Stakeholder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class StakeholderCreateAction
{
    use AsAction;

    public function handle($data): Stakeholder
    {
        try {
            DB::beginTransaction();

            $record = Stakeholder::create($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return $record;
    }
}
