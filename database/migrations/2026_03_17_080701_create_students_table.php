<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('registration_date')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('grade_id')->nullable();
            $table->string('section_id')->nullable();
            $table->string('gender')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('parents_name')->nullable();
            $table->string('parents_mobile_number')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('address')->nullable();
            $table->string('upload')->nullable();
            $table->bigInteger('institute_id')->unsigned();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
