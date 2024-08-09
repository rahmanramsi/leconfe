<?php

use App\Models\Session;
use App\Models\Registration;
use App\Models\Timeline;
use App\Models\ScheduledConference;
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
        Schema::create('registration_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ScheduledConference::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Timeline::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Session::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Registration::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_attendances');
    }
};
