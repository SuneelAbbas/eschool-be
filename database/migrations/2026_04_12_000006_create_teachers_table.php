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
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('institute_id');
            $table->unique(['institute_id', 'cnic_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
