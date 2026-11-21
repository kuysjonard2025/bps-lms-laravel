<?php // database/migrations/2026_01_01_000001_create_catalogs_table.php

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
            // author_id is nullable for periodical/non-author asset types (e.g., Newspaper, Magazine, Journal)
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('publisher_id')->constrained()->restrictOnDelete();
            $table->foreignId('general_reference_id')->constrained()->restrictOnDelete();
            $table->foreignId('asset_type_id')->constrained()->restrictOnDelete();

            // Bibliographic Details
            $table->string('title')->index();
            $table->string('isbn_issn', 20)->nullable()->unique();
            $table->string('call_number', 50)->index();
            $table->string('edition', 50)->nullable();
            $table->unsignedSmallInteger('publication_year')->index();
            $table->text('description')->nullable();

            // Composite Unique Constraint (Prevents exact duplicate bibliographic entries)
            $table->unique(
                ['title', 'author_id', 'asset_type_id', 'publisher_id', 'general_reference_id', 'publication_year'],
                'catalogs_unique_composite'
            );

            // Composite Index for OPAC filtering by Asset Type sorted by newest arrivals
            $table->index(['asset_type_id', 'created_at'], 'catalogs_asset_type_created_idx');

            $table->timestamps();

            // Index for fast ->latest() sorting on the main Catalogs table and OPAC
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
