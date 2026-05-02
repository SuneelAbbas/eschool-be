<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->unsignedBigInteger('fee_schedule_id')->nullable()->after('fee_type_id');
            $table->foreign('fee_schedule_id')->references('id')->on('fee_schedules')->onDelete('set null');
            $table->index('fee_schedule_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropForeign(['fee_schedule_id']);
            $table->dropColumn('fee_schedule_id');
        });
    }
};
