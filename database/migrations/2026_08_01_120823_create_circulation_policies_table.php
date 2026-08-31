<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('circulation_policies', function (Blueprint $table) {
            $table->id();

            // Unique descriptive name for clarity
            $table->string('name')->unique();

            // Foreign Keys
            $table->foreignId('patron_type_id')->constrained('patron_types')->restrictOnDelete();
            $table->foreignId('asset_type_id')->constrained('asset_types')->restrictOnDelete();

            // Policy Rules
            $table->unsignedInteger('max_borrow_limit')->default(3);
            $table->unsignedInteger('loan_duration_days')->default(7);

            // Financials
            $table->decimal('fine_per_day', 8, 2)->default(5.00);
            $table->decimal('max_fine_amount', 8, 2)->default(100.00);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index for active policy resolution
            $table->index(['patron_type_id', 'asset_type_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circulation_policies');
    }
};
