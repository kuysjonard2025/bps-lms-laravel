<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_loan_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patron_type_id')->constrained('patron_types')->restrictOnDelete();
            $table->unsignedInteger('max_borrow_limit')->default(3);
            $table->unsignedInteger('loan_duration_days')->default(7);
            $table->timestamps();

            // Index for quick lookup during the circulation/checkout process
            $table->index('patron_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_loan_limits');
    }
};
