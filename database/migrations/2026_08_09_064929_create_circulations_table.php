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
        Schema::create('circulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patron_id')->constrained('patrons')->cascadeOnDelete();
            $table->foreignId('accession_id')->constrained('accessions')->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('borrowed_at');
            $table->timestamp('due_at');
            $table->timestamp('returned_at')->nullable();

            $table->unsignedInteger('renewal_count')->default(0);

            // Need this if the Patron/Borrower have penalty to pay
            $table->string('transaction_number', 50)->nullable();

            $table->decimal('fine_amount', 8, 2)->default(0.00);
            $table->enum('status', ['borrowed', 'returned', 'overdue', 'lost'])->default('borrowed');

            $table->unique(['patron_id', 'accession_id', 'transaction_number']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circulations');
    }
};
