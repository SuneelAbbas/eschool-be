<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('fee_type_id');
            $table->unsignedBigInteger('fee_category_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['monthly', 'quarterly', 'annual', 'one_time']);
            $table->date('applicable_from');
            $table->date('applicable_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');
            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('cascade');
            $table->foreign('fee_category_id')->references('id')->on('fee_categories')->onDelete('set null');
            $table->index(['grade_id', 'fee_type_id', 'fee_category_id']);
            $table->index(['institute_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_schedules');
    }
};
