<?php

namespace App\Actions\Presenters;

use App\Models\Enums\PresenterStatus;
use App\Models\Presenter;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PresenterApprovedAction
{
    use AsAction;

    public function handle(Presenter $presenter): Presenter
    {
        try {
            DB::beginTransaction();
            
            $presenter->update([
                'status' => PresenterStatus::Approve,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return $presenter;
    }
}
