<?php

use App\Models\User;
use App\Models\RegistrationType;
use App\Models\ScheduledConference;
use Illuminate\Support\Facades\Schema;
use App\Models\Enums\RegistrationStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;

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
            $table->string('name');
            $table->integer('cost');
            $table->string('currency');
            $table->enum('state', Arr::except(RegistrationStatus::array(), RegistrationStatus::Trashed->value))->default(RegistrationStatus::Unpaid->value);
            $table->boolean('trashed')->default(false);
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
    }
};
