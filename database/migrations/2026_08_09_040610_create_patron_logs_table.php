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

            // Foreign key pointing to patrons.id
            $table->foreignId('patron_id')->constrained('patrons')->restrictOnDelete();

            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->date('log_date'); // Helpful for querying today's active logs
            $table->timestamps();

            // Index for faster log lookups during kiosk scanning
            $table->index(['patron_id', 'log_date']);
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
