<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropUnique('student_fee_unique');
            $table->unique(['student_id', 'fee_type_id', 'academic_year', 'month'], 'student_fee_unique')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropUnique('student_fee_unique');
            $table->unique(['student_id', 'fee_type_id', 'academic_year'], 'student_fee_unique');
        });
    }
};