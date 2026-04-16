<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_fees', function (Blueprint $table) {
            $table->string('academic_year', 9)->nullable()->after('fee_type_id');
            $table->dropUnique('grade_fee_unique');
            $table->unique(['grade_id', 'fee_type_id', 'academic_year'], 'grade_fee_unique');
            $table->index('academic_year');
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->string('academic_year', 9)->nullable()->after('fee_type_id');
            $table->dropUnique('student_fee_unique');
            $table->unique(['student_id', 'fee_type_id', 'academic_year'], 'student_fee_unique');
            $table->index('academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('grade_fees', function (Blueprint $table) {
            $table->dropUnique('grade_fee_unique');
            $table->unique(['grade_id', 'fee_type_id'], 'grade_fee_unique');
            $table->dropIndex(['academic_year']);
            $table->dropColumn('academic_year');
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropUnique('student_fee_unique');
            $table->unique(['student_id', 'fee_type_id'], 'student_fee_unique');
            $table->dropIndex(['academic_year']);
            $table->dropColumn('academic_year');
        });
    }
};
