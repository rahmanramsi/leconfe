<?php

namespace App\Actions\RegistrationTypes;

use App\Models\RegistrationType;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RegistrationTypeCreateAction
{
    use AsAction;

    public function handle(array $data): RegistrationType
    {
        try {
            DB::beginTransaction();

            $registrationType = RegistrationType::create($data);

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
