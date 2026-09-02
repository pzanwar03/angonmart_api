<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bd_divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bn_name')->nullable();
            $table->timestamps();
        });

        Schema::create('bd_districts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('division_id');
            $table->string('name');
            $table->string('bn_name')->nullable();
            $table->timestamps();
            $table->foreign('division_id')->references('id')->on('bd_divisions')->onDelete('cascade');
            $table->index('division_id');
        });

        Schema::create('bd_thanas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('district_id');
            $table->string('name');
            $table->string('bn_name')->nullable();
            $table->timestamps();
            $table->foreign('district_id')->references('id')->on('bd_districts')->onDelete('cascade');
            $table->index('district_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bd_thanas');
        Schema::dropIfExists('bd_districts');
        Schema::dropIfExists('bd_divisions');
    }
};
