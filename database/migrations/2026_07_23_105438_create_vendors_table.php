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
            $table->string('company_name', 100);
            $table->string('contact_person', 100)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
