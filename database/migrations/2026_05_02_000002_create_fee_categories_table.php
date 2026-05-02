<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->string('name'); // 'New', 'Old', 'RTE', 'Scholarship'
            $table->string('code', 10);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
            $table->unique(['institute_id', 'code']);
            $table->index(['institute_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_categories');
    }
};
