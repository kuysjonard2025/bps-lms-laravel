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
            $table->foreignId('catalog_id')->constrained()->restrictOnDelete();
            $table->foreignId('acquisition_id')->constrained()->restrictOnDelete();
            $table->string('accession_number')->unique();
            $table->string('batch_number')->index();
            $table->string('call_number');
            $table->string('condition')->default('new'); // new, good, fair, damaged, lost, missing
            $table->string('status')->default('available'); // available, on loan, reserved, under maintenance, dumped
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessions');
    }
};
