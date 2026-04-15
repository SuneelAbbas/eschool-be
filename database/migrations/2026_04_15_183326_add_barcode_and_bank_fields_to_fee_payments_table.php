<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->string('barcode_value')->nullable()->after('receipt_number');
            $table->string('bank_reference')->nullable()->after('barcode_value');
            $table->string('bank_account_id')->nullable()->after('bank_reference');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn(['barcode_value', 'bank_reference', 'bank_account_id']);
        });
    }
};
