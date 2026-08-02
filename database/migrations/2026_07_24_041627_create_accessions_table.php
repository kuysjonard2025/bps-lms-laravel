<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessions', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('catalog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('acquisition_id')->constrained()->cascadeOnDelete();

            // Identifiers & Circulation Tracking
            $table->string('accession_number', 20)->unique();
            $table->string('batch_number', 20)->unique();
            $table->string('call_number', 20);

            // Status & Condition Tracking
            $table->enum('condition', ['New', 'Good', 'Fair', 'Damaged'])->default('Good');
            $table->enum('status', [
                'Available',
                'On Loan',
                'Reserved',
                'Under Maintenance',
                'Lost',
                'Withdrawn'
            ])->default('Available');

            $table->date('acquired_date');
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Indexes for search and query performance
            $table->index(['acquisition_id', 'catalog_id', 'batch_number']);
            $table->index(['status', 'condition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessions');
    }
};
