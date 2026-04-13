<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['sibling', 'scholarship', 'need_based', 'merit', 'custom'])->default('custom');
            $table->decimal('percentage', 5, 2)->default(0)->comment('Discount percentage (0-100)');
            $table->decimal('fixed_amount', 10, 2)->default(0)->comment('Fixed discount amount');
            $table->json('conditions')->nullable()->comment('JSON conditions (e.g., sibling tiers: {2: 10, 3: 20})');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
            $table->unique(['institute_id', 'code']);
            $table->index('institute_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
