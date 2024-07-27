<?php

namespace App\Actions\RegistrationTypes;

use App\Models\RegistrationType;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RegistrationTypeUpdateAction
{
    use AsAction;

    public function handle(RegistrationType $registration_type, array $data): RegistrationType
    {
        try {
            DB::beginTransaction();

            $registration_type->update($data);

            if (data_get($data, 'meta')) {
                $registration_type->setManyMeta(data_get($data, 'meta'));
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return $registration_type;
    }
}
