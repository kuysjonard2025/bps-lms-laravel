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
            $table->string('rfid_tag')->unique();

            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('suffix', 10)->nullable();

            $table->text('address');
            $table->string('contact_number', 20)->unique();
            $table->string('email', 100)->unique();

            $table->foreignId('patron_type_id')->constrained('patron_types')->restrictOnDelete();

            // Academic Info foreign keys (aligned with AcademicInfo module)
            $table->foreignId('grade_level_id')->nullable()->constrained('grade_levels')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('strand_id')->nullable()->constrained('strands')->nullOnDelete();
            $table->foreignId('adviser_id')->nullable()->constrained('advisers')->nullOnDelete();

            $table->string('status', 20)->default('active'); // active | inactive | suspended

            $table->timestamps();

            $table->index(['patron_type_id', 'status']);
            $table->index(['grade_level_id', 'section_id']);
            $table->index(['department_id', 'adviser_id']);
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
