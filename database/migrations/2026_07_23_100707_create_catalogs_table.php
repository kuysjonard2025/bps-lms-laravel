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

            // Foreign Key Relationships
            $table->foreignId('author_id')->constrained()->restrictOnDelete();
            $table->foreignId('asset_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('publisher_id')->constrained()->restrictOnDelete();
            $table->foreignId('general_reference_id')->constrained()->restrictOnDelete();

            // Bibliographic Details
            $table->string('title')->index();
            $table->string('isbn_issn', 20)->nullable()->unique();
            $table->string('edition', 50)->nullable(); // Expanded to 50 to match validation
            $table->unsignedSmallInteger('publication_year');
            $table->text('description')->nullable();

            // Composite Unique Constraint
            $table->unique(
                ['title', 'author_id', 'asset_type_id', 'publisher_id', 'general_reference_id', 'publication_year'],
                'catalogs_unique_composite'
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
