<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('cnic_number');
            $table->string('subject')->nullable();
            $table->string('gender')->nullable();
            $table->string('mobile_number')->nullable();
            $table->date('join_date');
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('address')->nullable();
            $table->string('academic_qualification')->nullable();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->index('institute_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
