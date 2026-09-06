<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('circulations', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('patron_id')->constrained('patrons')->restrictOnDelete();
            $table->foreignId('accession_id')->constrained('accessions')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Dates & Timestamps
            $table->timestamp('borrowed_at');
            $table->timestamp('due_at');
            $table->timestamp('returned_at')->nullable();

            // Financials & Payment Tracking
            $table->decimal('fine_amount', 8, 2)->default(0.00); // Penalty fee
            $table->boolean('is_paid')->default(true); // Default true (zero fines are paid)
            $table->string('receipt_number', 50)->nullable()->index();

            // Status & Item State
            $table->string('status')->default('borrowed'); // 'borrowed', 'returned'
            $table->string('condition')->default('good');  // 'good', 'damaged', 'lost'

            $table->timestamps();

            // Indexes
            $table->index(['patron_id', 'status']);
            $table->index(['accession_id', 'status']);
            $table->index(['status', 'due_at']);
            $table->index('is_paid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circulations');
    }
};
