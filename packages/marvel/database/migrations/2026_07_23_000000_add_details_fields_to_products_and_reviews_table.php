<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('status');
            $table->json('highlights')->nullable()->after('is_verified');
            $table->json('specifications')->nullable()->after('highlights');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('photos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'highlights', 'specifications']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('is_verified');
        });
    }
};
