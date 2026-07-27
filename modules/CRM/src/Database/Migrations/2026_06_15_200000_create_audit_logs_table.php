<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Polymorphic subject: the model that was acted upon
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            // Action performed
            $table->string('action'); // e.g. customer_created, lead_status_changed
            // Human-readable description
            $table->text('description');
            // JSON metadata (diffs, old/new values, context)
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes for efficient querying
            $table->index('company_id');
            $table->index(['subject_type', 'subject_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
