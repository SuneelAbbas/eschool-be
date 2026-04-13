<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_payment_id');
            $table->unsignedBigInteger('student_fee_id');
            $table->decimal('amount_applied', 10, 2);
            $table->timestamps();

            $table->foreign('fee_payment_id')->references('id')->on('fee_payments')->onDelete('cascade');
            $table->foreign('student_fee_id')->references('id')->on('student_fees')->onDelete('cascade');
            $table->index('fee_payment_id');
            $table->index('student_fee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};
