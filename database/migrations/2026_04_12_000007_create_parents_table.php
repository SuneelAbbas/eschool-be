<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('cnic_number')->nullable();
            $table->string('gender')->nullable();
            $table->string('mobile_number');
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamps();
            
            $table->index('institute_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
