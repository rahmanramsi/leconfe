<?php

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
        Schema::create('bank_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ScheduledConference::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('currency');
            $table->text('detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method');
    }
};
