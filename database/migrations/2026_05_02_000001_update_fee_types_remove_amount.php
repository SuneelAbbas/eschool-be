<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove 'amount' from fee_types (amount is defined in fee_schedules)
        Schema::table('fee_types', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }

    public function down(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->after('code');
        });
    }
};
