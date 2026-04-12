<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_section', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('section_id')->constrained()->nullOnDelete();
            $table->boolean('is_class_teacher')->default(false)->after('subject_id');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_section', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['subject_id', 'is_class_teacher']);
        });
    }
};
