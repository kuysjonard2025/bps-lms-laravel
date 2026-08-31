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

            // Foreign Key to Patron Types (Filtered to Student in app logic)
            $table->foreignId('patron_type_id')->constrained('patron_types')->restrictOnDelete();
            $table->foreignId('asset_type_id')->constrained('asset_types')->restrictOnDelete();

            // Policy Rule Matrix
            $table->unsignedInteger('max_borrow_limit')->default(3);
            $table->unsignedInteger('loan_duration_days')->default(7);
            $table->unsignedInteger('max_renewals')->default(1);
            $table->unsignedInteger('grace_period_days')->default(0);

            // Financials
            $table->decimal('fine_per_day', 8, 2)->default(5.00);
            $table->decimal('max_fine_amount', 8, 2)->default(100.00);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique matrix pair constraint
            $table->unique(['patron_type_id', 'asset_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circulation_policies');
    }
};
