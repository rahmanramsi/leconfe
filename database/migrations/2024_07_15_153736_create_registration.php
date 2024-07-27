<?php

use App\Models\Enums\RegistrationStatus;
use App\Models\RegistrationType;
use App\Models\ScheduledConference;
use App\Models\User;
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
        Schema::create('registration_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ScheduledConference::class)->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('cost');
            $table->integer('quota');
            $table->string('currency');
            $table->boolean('active')->default(true);
            $table->timestamp('opened_at');
            $table->timestamp('closed_at');
            $table->timestamps();
        });
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ScheduledConference::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(RegistrationType::class);
            $table->boolean('is_trashed')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_types');
        Schema::dropIfExists('registration');
        Schema::dropIfExists('registration_notifications');
        Schema::dropIfExists('registration_options');
    }
};
