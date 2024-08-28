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
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignIdFor(Submission::class)->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('registration_payments', function (Blueprint $table) {
            $table->unsignedInteger('level')->default(RegistrationType::LEVEL_PARTICIPANT);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeignIdFor(Submission::class);
        });

        Schema::table('registration_payments', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
