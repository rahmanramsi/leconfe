<?php

namespace App\Utils\UpgradeSchemas;

use App\Models\RegistrationType;
use App\Models\Submission;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradeBeta4 extends UpgradeBase
{
    public function run(): void
    {
        $this->addColumns();
    }

    public function addColumns(): void
    {
        if (! Schema::hasColumn('registrations', (new Submission)->getForeignKey())) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->foreignIdFor(Submission::class)->nullable()->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('registration_payments', 'level')) {
            Schema::table('registration_payments', function (Blueprint $table) {
                $table->unsignedInteger('level')->default(RegistrationType::LEVEL_PARTICIPANT);
            });
        }
    }
}
