<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_id');
            $table->string('name');
            $table->string('room_no')->nullable();
            $table->integer('capacity')->default(30);
            $table->string('class_teacher')->nullable();
            $table->timestamps();
            $table->index('grade_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
