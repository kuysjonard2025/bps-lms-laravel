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

            // Supplier: sells to the library. Donor: gives items to the library.
            $table->string('type', 20)->default('supplier');

            // Company Details
            $table->string('company_name', 100)->index();
            $table->string('company_address', 255)->nullable(); // required for suppliers (enforced in validation)

            // Contact Person Details
            $table->string('contact_first_name', 100)->index();
            $table->string('contact_middle_name', 100)->nullable();
            $table->string('contact_last_name', 100)->index();
            $table->string('contact_number', 20)->nullable()->unique(); // required for suppliers (enforced in validation)
            $table->string('contact_email', 100)->nullable()->unique(); // required for suppliers (enforced in validation)

            // Prevent duplicate vendor branches at the same address
            $table->unique(['company_name', 'company_address'], 'vendors_company_address_unique');

            $table->timestamps();

            // Fast dropdown lookups (suppliers or donors, sorted by name)
            $table->index(['type', 'company_name'], 'vendors_type_name_index');

            // Index for fast ->latest() sorting in the Vendors table
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
