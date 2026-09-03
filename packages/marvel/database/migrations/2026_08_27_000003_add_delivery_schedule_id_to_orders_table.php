<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_schedule_id')->nullable()->after('delivery_time');
            $table->foreign('delivery_schedule_id')
                ->references('id')
                ->on('delivery_schedules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_schedule_id']);
            $table->dropColumn('delivery_schedule_id');
        });
    }
};
