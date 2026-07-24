<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acquisitions', function (Blueprint $table) {
            $table->id();

            // Reference to catalog record
            $table->foreignId('catalog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();

            // Transaction & Log Details
            $table->string('acquisition_number')->unique(); // e.g., ACQ-2026-001
            $table->string('transaction_number', 50); // e.g., PO / Invoice / OR #
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->date('received_date');
            $table->text('remarks')->nullable();

            $table->unique(['transaction_number', 'catalog_id', 'vendor_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acquisitions');
    }
};
