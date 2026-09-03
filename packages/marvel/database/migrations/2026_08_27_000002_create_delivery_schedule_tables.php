<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('delivery_zone_schedule_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_zone_id');
            $table->unsignedBigInteger('delivery_schedule_id');
            $table->double('charge')->default(0);
            $table->timestamps();
            $table->foreign('delivery_zone_id')
                ->references('id')
                ->on('delivery_zones')
                ->onDelete('cascade');
            $table->foreign('delivery_schedule_id')
                ->references('id')
                ->on('delivery_schedules')
                ->onDelete('cascade');
            $table->unique(
                ['delivery_zone_id', 'delivery_schedule_id'],
                'delivery_zone_schedule_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zone_schedule_charges');
        Schema::dropIfExists('delivery_schedules');
    }
};
