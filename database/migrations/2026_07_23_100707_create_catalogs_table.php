<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();

            // Core Relationships
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publisher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('general_reference_id')->constrained()->cascadeOnDelete();

            // Catalog Bibliographic Details
            $table->string('title');
            $table->string('isbn_issn', 20)->nullable();
            $table->string('edition', 20)->nullable();
            $table->year('publication_year');
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
