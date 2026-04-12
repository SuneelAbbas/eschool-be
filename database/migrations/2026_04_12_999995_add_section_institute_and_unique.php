<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->unsignedBigInteger('institute_id')->nullable()->after('grade_id');
            $table->unique(['grade_id', 'name'], 'sections_grade_name_unique');
        });

        DB::statement('UPDATE sections s SET s.institute_id = (SELECT g.institute_id FROM grades g WHERE g.id = s.grade_id LIMIT 1)');
        Schema::table('sections', function (Blueprint $table) {
            $table->unsignedBigInteger('institute_id')->nullable(false)->change();
            $table->index('institute_id');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique('sections_grade_name_unique');
            $table->dropColumn('institute_id');
        });
    }
};
