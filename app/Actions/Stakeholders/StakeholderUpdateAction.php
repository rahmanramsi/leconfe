<?php

namespace App\Actions\Stakeholders;

use App\Models\Stakeholder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class StakeholderUpdateAction
{
    use AsAction;

    public function handle(Stakeholder $stakeholder, array $data): Stakeholder
    {
        try {
            DB::beginTransaction();

            $stakeholder->update($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return $stakeholder;
    }
}
