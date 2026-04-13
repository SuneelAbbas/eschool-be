<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['monthly', 'one_time'])->default('monthly');
            $table->integer('due_day')->nullable()->comment('Day of month for monthly fees (1-28)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
            $table->unique(['institute_id', 'name']);
            $table->index('institute_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
};
