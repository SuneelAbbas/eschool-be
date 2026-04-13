<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('exam_types');
        
        if (!in_array('type', $columns)) {
            Schema::table('exam_types', function (Blueprint $table) {
                $table->string('type', 20)->default('unit_test')->after('code');
            });
        }
        
        if (!in_array('code', $columns)) {
            Schema::table('exam_types', function (Blueprint $table) {
                $table->string('code', 50)->nullable()->after('institute_id');
            });
        }
    }

    public function down(): void
    {
    }
};
