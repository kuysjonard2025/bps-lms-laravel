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
            $table->foreignId('acquisition_id')->constrained()->restrictOnDelete();

            $table->string('accession_number', 100)->unique();

            // Condition: new, good, minor damage, severe damaged, lost, missing
            $table->string('condition', 50)->default('new')->index();

            // Status: available, on loan, stored, under maintenance, dumped
            $table->string('status', 50)->default('stored')->index();

            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessions');
    }
};
