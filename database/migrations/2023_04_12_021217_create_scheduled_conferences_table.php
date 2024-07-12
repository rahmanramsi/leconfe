<?php

use App\Models\Conference;
use App\Models\Enums\ScheduledConferenceState;
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
        Schema::create('scheduled_conferences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Conference::class)->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('title');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->enum('state', ScheduledConferenceState::array())->default(ScheduledConferenceState::Draft->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
