<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('class_teacher')->nullable();
            $table->string('room_no')->nullable();
            $table->timestamps();
            
            $table->index('grade_id');
            $table->index('section_id');
            $table->unique(['grade_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
