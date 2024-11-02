<?php

use App\Models\Registration;
use App\Models\ScheduledConference;
use App\Models\Session;
use App\Models\Timeline;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
