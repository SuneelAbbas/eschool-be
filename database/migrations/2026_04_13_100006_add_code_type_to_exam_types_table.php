<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE exam_types ADD COLUMN code VARCHAR(50) NULL AFTER institute_id");
        DB::statement("ALTER TABLE exam_types ADD COLUMN type VARCHAR(20) DEFAULT 'unit_test' AFTER code");
    }

    public function down(): void
    {
        Schema::table('exam_types', function (Blueprint $table) {
            $table->dropColumn(['code', 'type']);
        });
    }
};
