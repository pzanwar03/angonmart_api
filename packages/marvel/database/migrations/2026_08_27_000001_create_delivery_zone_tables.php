<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('charge')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('delivery_zone_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_zone_id');
            $table->enum('location_type', ['division', 'district', 'thana']);
            $table->unsignedBigInteger('location_id');
            $table->timestamps();
            $table->foreign('delivery_zone_id')->references('id')->on('delivery_zones')->onDelete('cascade');
            $table->unique(['location_type', 'location_id'], 'delivery_zone_area_location_unique');
            $table->index(['location_type', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zone_areas');
        Schema::dropIfExists('delivery_zones');
    }
};
