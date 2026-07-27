<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_up_tasks', function (Blueprint $table) {
            // Drop foreign keys and columns
            $table->dropForeign(['lead_id']);
            $table->dropColumn('lead_id');
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');

            // Add polymorphic relation
            $table->nullableMorphs('related');

            // Add new fields
            $table->dateTime('reminder_at')->nullable()->after('due_date');
            $table->string('priority')->default('medium')->after('reminder_at');
            $table->renameColumn('description', 'notes');
        });
    }

    public function down(): void
    {
        Schema::table('follow_up_tasks', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->dropMorphs('related');
            $table->dropColumn(['reminder_at', 'priority']);
            $table->renameColumn('notes', 'description');
        });
    }
};
