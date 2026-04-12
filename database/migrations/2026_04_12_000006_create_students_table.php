<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('registration_date')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('gender')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('parents_name')->nullable();
            $table->string('parents_mobile_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('address')->nullable();
            $table->string('upload')->nullable();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->timestamps();
            $table->index('institute_id');
            $table->index('section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
