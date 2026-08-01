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
        Schema::create('patrons', function (Blueprint $table) {
            $table->id();
            $table->string('patron_id')->unique();
            $table->string('first_name', 50);
            $table->string('middle_name', 50);
            $table->string('last_name', 50);
            $table->string('suffix', 10)->nullable();
            $table->string('address', 255);
            $table->string('contact_number', 20)->unique();
            $table->string('email', 100)->unique();

            // Foreign Key to patron_types table
            $table->foreignId('patron_type_id')->constrained('patron_types')->cascadeOnDelete();

            $table->foreignId('grade_level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');

            $table->unique(['first_name', 'middle_name', 'last_name', 'suffix'], 'patrons_full_name_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrons');
    }
};


