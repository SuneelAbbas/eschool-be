<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'jazzcash', 'easypaisa', 'cheque'])->default('cash');
            $table->string('receipt_number')->nullable();
            $table->string('transaction_id')->nullable()->comment('Bank/JazzCash transaction ID');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('month')->nullable()->comment('Month for monthly fees (e.g., 2026-04)');
            $table->string('academic_year')->nullable()->comment('Academic year (e.g., 2025-2026)');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
            $table->index('student_id');
            $table->index('receipt_number');
            $table->index('month');
            $table->index('payment_date');
            $table->index('received_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
