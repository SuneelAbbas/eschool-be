<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->string('fee_breakdown')->nullable()->comment('JSON: fee type breakdown');
            $table->date('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('bank_reference')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('transaction_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_receipts');
    }
};