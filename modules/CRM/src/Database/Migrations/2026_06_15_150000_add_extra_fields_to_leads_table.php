<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('title');
            $table->string('phone')->nullable()->after('contact_name');
            $table->string('email')->nullable()->after('phone');
            $table->string('source')->nullable()->after('email');
            $table->date('expected_close_date')->nullable()->after('value');
            $table->text('notes')->nullable()->after('expected_close_date');
            
            // Rename value to estimated_value
            $table->renameColumn('value', 'estimated_value');
            
            // Make pipeline_stage_id nullable
            $table->foreignId('pipeline_stage_id')->nullable()->change();
            
            // Update default status to 'new' instead of 'open'
            $table->string('status')->default('new')->change();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'phone', 'email', 'source', 'expected_close_date', 'notes']);
            $table->renameColumn('estimated_value', 'value');
            $table->foreignId('pipeline_stage_id')->nullable(false)->change();
            $table->string('status')->default('open')->change();
        });
    }
};
