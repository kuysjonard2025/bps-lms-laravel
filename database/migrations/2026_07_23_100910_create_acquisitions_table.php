<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acquisitions', function (Blueprint $table) {
            $table->id();

            // Reference to catalog record and vendor
            $table->foreignId('catalog_id')->constrained()->restrictOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();

            // Transaction & Log Details
            $table->string('acquisition_number')->unique(); // e.g., ACQ-2026-001
            $table->string('transaction_number', 50); // e.g. Invoice / OR # / PO Number

            // Fulfillment Status: pending (ordered), partial, delivered
            $table->string('status', 30)->default('pending')->index();

            $table->string('delivery_type')->default('purchase'); // purchase or donation

            // Split quantities for partial shipment tracking
            $table->integer('quantity_ordered')->default(1);
            $table->integer('quantity_received')->default(0);

            $table->decimal('unit_cost', 10, 2)->default(0.00);

            $table->date('delivery_date');

            // Nullable to allow saving pending purchase orders before delivery
            $table->date('received_date')->nullable();

            $table->text('remarks')->nullable();

            // Allow same invoice/transaction number for different catalog items,
            // but prevent duplicate entries for the exact same book under the same invoice.
            $table->unique(['transaction_number', 'catalog_id', 'vendor_id'], 'acquisitions_txn_catalog_vendor_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acquisitions');
    }
};
