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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('log_name')->default('default')->index(); // e.g., 'catalog', 'accession', 'patron'
            $table->string('event'); // 'created', 'updated', 'deleted'
            $table->nullableMorphs('subject'); // Polymorphic link: subject_type & subject_id
            $table->string('description'); // e.g., "Created new catalog record #12"
            $table->json('properties')->nullable(); // Stores before/after values for updates
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
