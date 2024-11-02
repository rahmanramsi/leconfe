<?php

namespace App\Utils\UpgradeSchemas;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Upgrade110 extends UpgradeBase
{
    public function run(): void
    {
        // Check order_column in tracks table, create before column created_at if not exists
        if (! Schema::hasColumn('tracks', 'order_column')) {
            Schema::table('tracks', function (Blueprint $table) {
                $table->unsignedInteger('order_column')->nullable()->before('created_at');
            });
        }
    }
}
