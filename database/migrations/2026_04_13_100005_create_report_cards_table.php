<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2);
            $table->decimal('marks_obtained', 8, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('grade', 5)->nullable();
            $table->integer('section_position')->nullable();
            $table->integer('grade_position')->nullable();
            $table->text('remarks')->nullable();
            $table->json('subject_results')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'section_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
