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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();$table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('join_date');
            $table->string('cnic_number');
            $table->string('subject');
            $table->string('gender');
            $table->string('mobile_number');
            $table->string('date_of_birth');
            $table->string('blood_group');
            $table->string('address');
            $table->string('academic_qualification');
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
        Schema::dropIfExists('teachers');
    }
};
