<?php

namespace App\Actions\RegistrationTypes;

use App\Models\RegistrationType;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RegistrationTypeUpdateAction
{
    use AsAction;

    public function handle(RegistrationType $registrationType, array $data): RegistrationType
    {
        try {
            DB::beginTransaction();

            $registrationType->update($data);

            if (data_get($data, 'meta')) {
                $registrationType->setManyMeta(data_get($data, 'meta'));
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return $registrationType;
    }
}
