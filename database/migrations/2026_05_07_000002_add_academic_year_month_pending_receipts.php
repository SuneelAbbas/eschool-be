<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_receipts', function (Blueprint $table) {
            $table->string('academic_year', 9)->nullable()->after('transaction_id');
            $table->string('month', 20)->nullable()->after('academic_year');
            $table->index('academic_year');
            $table->index('month');
        });
    }

    public function down(): void
    {
        Schema::table('pending_receipts', function (Blueprint $table) {
            $table->dropIndex(['academic_year', 'month']);
            $table->dropColumn(['academic_year', 'month']);
        });
    }
};