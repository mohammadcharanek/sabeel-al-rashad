<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number', 40)->unique();
            $table->string('student_name', 150);
            $table->date('date_of_birth');
            $table->foreignId('educational_stage_id')->constrained()->restrictOnDelete();
            $table->string('guardian_name', 150);
            $table->string('guardian_phone', 20);
            $table->string('guardian_email')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_original_name')->nullable();
            $table->enum('status', ['pending', 'under_review', 'accepted', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('submitted_at')->index();
            $table->timestamps();
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_applications');
    }
};
