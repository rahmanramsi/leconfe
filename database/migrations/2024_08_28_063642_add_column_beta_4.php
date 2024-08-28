<?php

use App\Models\Submission;
use App\Models\RegistrationType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if(!Schema::hasColumn('registrations', (new Submission())->getForeignKey())) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->foreignIdFor(Submission::class)->nullable()->constrained()->cascadeOnDelete();
            });
        }
        
        if(!Schema::hasColumn('registration_payments', 'level')) {
            Schema::table('registration_payments', function (Blueprint $table) {
                $table->unsignedInteger('level')->default(RegistrationType::LEVEL_PARTICIPANT);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // when database rollback called, all column above gonna dropped in '2024_07_15_153736_create_registration.php'
    }
};
