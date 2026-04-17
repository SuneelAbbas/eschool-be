<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->boolean('is_inherited')->default(false)->after('is_active')->comment('True if inherited from grade fee');
            $table->unsignedBigInteger('inherited_from_grade_fee_id')->nullable()->after('is_inherited')->comment('Source grade fee ID');
            $table->decimal('prorate_percentage', 5, 2)->default(100)->after('inherited_from_grade_fee_id')->comment('100 = full, 50 = half for mid-month join');
            $table->enum('status', ['pending', 'partial', 'paid', 'waived'])->default('pending')->after('prorate_percentage');
            
            $table->foreign('inherited_from_grade_fee_id')->references('id')->on('grade_fees')->onDelete('set null');
            $table->index('is_inherited');
            $table->index('status');
        });

        Schema::create('student_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_fee_id');
            $table->unsignedBigInteger('fee_payment_id');
            $table->decimal('amount_applied', 10, 2);
            $table->timestamps();

            $table->foreign('student_fee_id')->references('id')->on('student_fees')->onDelete('cascade');
            $table->foreign('fee_payment_id')->references('id')->on('fee_payments')->onDelete('cascade');
            $table->index('student_fee_id');
            $table->index('fee_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_payments');

        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropForeign(['inherited_from_grade_fee_id']);
            $table->dropIndex(['is_inherited']);
            $table->dropIndex(['status']);
            $table->dropColumn(['is_inherited', 'inherited_from_grade_fee_id', 'prorate_percentage', 'status']);
        });
    }
};
