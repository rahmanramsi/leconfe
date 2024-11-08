<?php

namespace App\Actions\System;

use App\Actions\Leconfe\Relink;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class StorageLinkValidate
{
    use AsAction;

    public function handle()
    {
        try {
            $link = public_path('storage');

            if (file_exists($link) && ! is_link($link)) {
                Relink::run();

                Log::info('Storage link has been relinked.');
            }
        } catch (\Throwable $th) {
            Log::warning($th->getMessage());
        }
    }
}
