<?php

namespace App\Actions\RegistrationTypes;

use App\Models\RegistrationType;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RegistrationTypeDeleteAction
{
    use AsAction;

    public function handle(RegistrationType $registrationType)
    {
        try {
            DB::beginTransaction();

            $registrationType->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
