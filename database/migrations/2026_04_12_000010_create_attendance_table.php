<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('section_id');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'date']);
            $table->index(['section_id', 'date']);
            $table->index(['student_id', 'date']);
            $table->unique(['student_id', 'date'], 'attendance_student_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
