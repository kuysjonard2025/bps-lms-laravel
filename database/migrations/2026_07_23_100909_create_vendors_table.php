<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 50);
            $table->string('address', 100);
            $table->string('contact_person', 100)->unique();
            $table->string('contact_number', 20)->unique();
            $table->string('email', 50)->unique();
            $table->unique(['company_name', 'address']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
