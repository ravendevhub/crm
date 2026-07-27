<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make customer_id nullable in quotations
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->change();
        });

        // 2. Create quotation_items table
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00); // e.g. 10.00 for 10%
            $table->decimal('total', 15, 2);
            $table->timestamps();

            // Indexes
            $table->index('company_id');
            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable(false)->change();
        });
    }
};
