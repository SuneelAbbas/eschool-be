<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('fee_category_id')->nullable()->after('institute_id');
            $table->foreign('fee_category_id')->references('id')->on('fee_categories')->onDelete('set null');
            $table->index('fee_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['fee_category_id']);
            $table->dropColumn('fee_category_id');
        });
    }
};
