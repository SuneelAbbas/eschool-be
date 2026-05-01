<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->string('current_academic_year', 9)->nullable()->after('plan_id');
            $table->index('current_academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropIndex(['current_academic_year']);
            $table->dropColumn('current_academic_year');
        });
    }
};
