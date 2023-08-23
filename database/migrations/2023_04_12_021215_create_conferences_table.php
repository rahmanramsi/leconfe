<?php

use App\Models\Enums\ConferenceStatus;
use App\Models\Enums\ConferenceType;
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
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('status', ConferenceStatus::array())->default(ConferenceStatus::Active->value);
            $table->enum('type', ConferenceType::array())->default(ConferenceType::Offline->value);
            $table->integer('is_current')->default(0);
            $table->timestamps();
        });
        
        Schema::create('conferences_meta', function (Blueprint $table) {
            $table->id();
            $table->string('metable_type');
            $table->unsignedBigInteger('metable_id');
            $table->string('type')->default('null');
            $table->string('key')->index();
            $table->longtext('value');

            $table->unique(['metable_type', 'metable_id', 'key']);
            $table->index(['key', 'metable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conferences');
    }
};
