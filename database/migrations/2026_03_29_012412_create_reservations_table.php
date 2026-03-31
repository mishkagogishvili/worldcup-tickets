<?php

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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('ticket_category_id')->constrained();
            $table->integer('quantity');
            $table->decimal('total_price', 8, 2);
            $table->enum('status', ['pending', 'confirmed', 'expired', 'cancelled'])->default('pending');
            $table->index(['status', 'expires_at']);
            $table->timestamp('expires_at');
            $table->string('idempotency_key')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
