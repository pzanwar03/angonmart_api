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
            $table->json('video_file')->nullable()->after('video');
            $table->boolean('is_preorder')->default(false)->after('in_stock');
            $table->timestamp('preorder_available_at')->nullable()->after('is_preorder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['video_file', 'is_preorder', 'preorder_available_at']);
        });
    }
};
