<?php

use App\Models\Enums\RegistrationPaymentState;
use App\Models\Registration;
use App\Models\RegistrationType;
use App\Models\ScheduledConference;
use App\Models\Submission;
use App\Models\User;
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
        Schema::create('registration_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ScheduledConference::class)->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedInteger('level')->default(1);
            $table->integer('cost');
            $table->integer('quota');
            $table->string('currency');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('order_column')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ScheduledConference::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(RegistrationType::class);
            $table->foreignIdFor(Submission::class)->nullable()->constrained()->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('registration_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ScheduledConference::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Registration::class)->constrained()->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('name');
            $table->unsignedInteger('level')->default(RegistrationType::LEVEL_PARTICIPANT);
            $table->text('description')->nullable();
            $table->integer('cost');
            $table->string('currency');
            $table->string('state')->default(RegistrationPaymentState::Unpaid->value);
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
