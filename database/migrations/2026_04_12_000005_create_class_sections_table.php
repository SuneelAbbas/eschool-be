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
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('section_id');
            $table->string('class_teacher')->nullable();
            $table->string('room_no')->nullable();
            $table->timestamps();
            $table->index('grade_id');
            $table->index('section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
