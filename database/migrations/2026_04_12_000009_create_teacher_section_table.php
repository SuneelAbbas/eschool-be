<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_section', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('section_id');
            $table->string('subject')->nullable();
            $table->timestamps();
            $table->index('teacher_id');
            $table->index('section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_section');
    }
};
