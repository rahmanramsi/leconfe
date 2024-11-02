<?php

use App\Models\Conference;
use App\Models\ScheduledConference;
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
        Schema::create('stakeholder_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Conference::class)->default(0);
            $table->foreignIdFor(ScheduledConference::class)->default(0);
            $table->unsignedInteger('type');
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('order_column')->nullable();
            $table->boolean('is_shown')->default(true);
            $table->timestamps();
        });

        Schema::create('stakeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Conference::class)->default(0);
            $table->foreignIdFor(ScheduledConference::class)->default(0);
            $table->unsignedInteger('type');
            $table->foreignId('level_id')->nullable()->constrained('stakeholder_levels')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('order_column')->nullable();
            $table->boolean('is_shown')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stakeholders');
        Schema::dropIfExists('stakeholder_levels');
    }
};
