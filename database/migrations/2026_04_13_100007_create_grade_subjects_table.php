<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_compulsory')->default(true);
            $table->integer('max_marks')->nullable();
            $table->timestamps();

            $table->unique(['grade_id', 'subject_id']);
            $table->index(['grade_id', 'is_compulsory']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_subjects');
    }
};
