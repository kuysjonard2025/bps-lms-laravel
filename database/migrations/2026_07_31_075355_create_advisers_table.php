<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advisers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('first_name', 100)->index();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->index();
            $table->string('contact_number', 20)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->timestamps();

            $table->unique(['department_id', 'first_name', 'middle_name', 'last_name'], 'advisers_dept_fullname_unique');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advisers');
    }
};
