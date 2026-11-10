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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 120);
            $table->string('phone', 30);
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->unsignedSmallInteger('service_duration_minutes');
            $table->decimal('service_price', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->index(['appointment_date', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
