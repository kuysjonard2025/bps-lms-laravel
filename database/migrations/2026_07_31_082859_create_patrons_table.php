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
            $table->string('school_id')->unique();

            // Made nullable in case RFID is assigned later, explicit length for RFID UID
            $table->string('rfid_tag', 64)->nullable()->unique();

            $table->string('first_name', 50);
            $table->string('middle_name', 50);
            $table->string('last_name', 50);
            $table->string('suffix', 10)->nullable();
            $table->string('address', 255);
            $table->string('contact_number', 20)->unique();
            $table->string('email', 100)->unique();

            // Foreign Keys
            $table->foreignId('patron_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('grade_level_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->restrictOnDelete();

            $table->string('status')->default('active'); // e.g., 'active', 'inactive', 'suspended'

            // Name Uniqueness Constraint
            $table->unique(['first_name', 'middle_name', 'last_name', 'suffix'], 'patrons_full_name_unique');
            $table->timestamps();

            // Fast lookup index for RFID scanning during circulation (check-in/check-out)
            $table->index('rfid_tag');
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


