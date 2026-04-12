<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('institute_id')
                ->references('id')
                ->on('institutes')
                ->cascadeOnDelete();
        });

        Schema::table('institutes', function (Blueprint $table) {
            $table->foreign('admin_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans')
                ->nullOnDelete();
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->foreign('institute_id')
                ->references('id')
                ->on('institutes')
                ->cascadeOnDelete();
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('grade_id')
                ->references('id')
                ->on('grades')
                ->cascadeOnDelete();
        });

        Schema::table('class_sections', function (Blueprint $table) {
            $table->foreign('grade_id')
                ->references('id')
                ->on('grades')
                ->cascadeOnDelete();
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->cascadeOnDelete();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('institute_id')
                ->references('id')
                ->on('institutes')
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->nullOnDelete();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->foreign('institute_id')
                ->references('id')
                ->on('institutes')
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->foreign('institute_id')
                ->references('id')
                ->on('institutes')
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->nullOnDelete();
        });

        Schema::table('teacher_section', function (Blueprint $table) {
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnDelete();
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_section', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['section_id']);
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropForeign(['institute_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['student_id']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['institute_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['institute_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['section_id']);
        });

        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['section_id']);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['institute_id']);
        });

        Schema::table('institutes', function (Blueprint $table) {
            $table->dropForeign(['admin_user_id']);
            $table->dropForeign(['plan_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institute_id']);
        });
    }
};
