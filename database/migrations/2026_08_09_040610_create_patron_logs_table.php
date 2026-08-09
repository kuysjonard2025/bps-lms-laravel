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
        Schema::create('patron_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patron_id')->constrained('patrons')->cascadeOnDelete();
            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->date('log_date'); // Helpful for querying today's active logs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patron_logs');
    }
};
