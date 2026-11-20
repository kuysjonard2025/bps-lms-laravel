<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('circulation_penalties', function (Blueprint $table) {
            $table->id();

            // Foreign Keys — one rule per patron_type + asset_type pair
            $table->foreignId('patron_type_id')->constrained('patron_types')->restrictOnDelete();
            $table->foreignId('asset_type_id')->constrained('asset_types')->restrictOnDelete();

            // Overdue limit after which overdue items transition to lost; daily fine scales by unit_price / lost_after_days
            $table->unsignedInteger('lost_after_days')->default(14);

            $table->timestamps();

            $table->unique(['patron_type_id', 'asset_type_id'], 'circulation_penalties_patron_asset_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circulation_penalties');
    }
};
