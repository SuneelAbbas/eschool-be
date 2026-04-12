<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('type')->nullable();
            $table->string('city')->nullable();
            $table->integer('no_of_students')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutes');
    }
};
