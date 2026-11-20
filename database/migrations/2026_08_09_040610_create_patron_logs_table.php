<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patron_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patron_id')->constrained('patrons')->restrictOnDelete();

            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->date('log_date');
            $table->timestamps();

            $table->index(['patron_id', 'log_date']);
            $table->index('log_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patron_logs');
    }
};
