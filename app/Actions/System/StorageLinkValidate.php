<?php

namespace App\Actions\System;

use Illuminate\Support\Facades\File;
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
                File::deleteDirectory($link);
            }

            if (! file_exists($link)) {
                File::link(storage_path('app/public'), $link);
            }
        } catch (\Throwable $th) {
            Log::warning($th->getMessage());
        }
    }
}
