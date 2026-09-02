<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Converts the Marvel package from a multi-vendor marketplace into a
 * single-vendor storefront: drops every table/column that only existed to
 * support multiple shops/vendors (shops, balances, withdraws, commissions,
 * ownership transfers, became-sellers, per-shop pivots, vendor flash-sale
 * requests, and the `shop_id`/`parent_id`/`visibility` columns used to scope
 * records to a shop or split an order into per-shop child orders).
 *
 * This is a one-way conversion; the down() method is a best-effort schema
 * restore only (no data is preserved for the dropped tables/columns).
 */
return new class extends Migration
{
    /**
     * The tables that existed purely to support multiple vendors/shops.
     *
     * @var string[]
     */
    private array $multiVendorTables = [
        // pivot / dependent tables first (in case DBs without FK cascade need this order)
        'user_shop',
        'category_shop',
        'store_notice_shop',
        'flash_sale_requests_products',
        'flash_sale_requests',
        'ownership_transfers',
        'became_sellers',
        'withdraws',
        'balances',
        'commissions',
        'shops',
    ];

    /**
     * Tables (and the shop-scoping column(s) on them) that remain in a
     * single-vendor system but no longer need a `shop_id` foreign key.
     *
     * @var array<string, string[]>
     */
    private array $shopIdColumns = [
        'users' => ['shop_id'],
        'products' => ['shop_id'],
        'orders' => ['shop_id', 'parent_id'],
        'attributes' => ['shop_id'],
        'coupons' => ['shop_id'],
        'reviews' => ['shop_id'],
        'questions' => ['shop_id'],
        'refunds' => ['shop_id'],
        'faqs' => ['shop_id'],
        'terms_and_conditions' => ['shop_id'],
        'refund_policies' => ['shop_id'],
        'conversations' => ['shop_id'],
        'participants' => ['shop_id'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the shop-scoping / per-shop-split columns (and their FKs) first,
        // since some of them reference the `shops` table we're about to drop.
        foreach ($this->shopIdColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (!Schema::hasColumn($table, $column)) {
                        continue;
                    }

                    try {
                        $blueprint->dropForeign([$column]);
                    } catch (\Throwable $e) {
                        // No FK with the conventional name (or it was already dropped) - ignore.
                    }

                    $blueprint->dropColumn($column);
                }
            });
        }

        // Drop the `visibility` column on products (per-shop maintenance mode no longer applies).
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'visibility')) {
            Schema::table('products', function (Blueprint $blueprint) {
                $blueprint->dropColumn('visibility');
            });
        }

        // Now it's safe to drop the multi-vendor-only tables themselves.
        foreach ($this->multiVendorTables as $table) {
            Schema::dropIfExists($table);
        }
    }

    /**
     * Reverse the migrations.
     *
     * This is a one-way conversion. We only restore the dropped columns
     * (nullable, no FK, no data) so the migration contract stays valid;
     * the multi-vendor tables themselves are not recreated.
     */
    public function down(): void
    {
        foreach ($this->shopIdColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (!Schema::hasColumn($table, $column)) {
                        $blueprint->unsignedBigInteger($column)->nullable();
                    }
                }
            });
        }

        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'visibility')) {
            Schema::table('products', function (Blueprint $blueprint) {
                $blueprint->string('visibility')->nullable();
            });
        }
    }
};
