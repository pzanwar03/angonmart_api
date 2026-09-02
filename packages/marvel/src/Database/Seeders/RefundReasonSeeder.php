<?php

namespace Marvel\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class RefundReasonSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('refund_reasons')->insert([
            [
                "name" => "Product Not as Described",
                "slug" => "product-not-as-described",
                "language" => "en",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Wrong Item Shipped",
                "slug" => "wrong-item-shipped",
                "language" => "en",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Damaged Item",
                "slug" => "damaged-item",
                "language" => "en",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Cancelled Order",
                "slug" => "cancelled-order",
                "language" => "en",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Late Delivery",
                "slug" => "late-delivery",
                "language" => "en",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Item Not Needed",
                "slug" => "item-not-needed",
                "language" => "en",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Changed Mind",
                "slug" => "changed-mind",
                "language" => "en",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Others",
                "slug" => "others",
                "language" => "en",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            ...$this->getBanglaDummyData()
        ]);
    }

    /**
     * getBanglaDummyData
     *
     * @return array
     */
    private function getBanglaDummyData(): array
    {

        if (!TRANSLATION_ENABLED) {
            return [];
        }

        return [
            [
                "name" => "Product Not as Described",
                "slug" => "product-not-as-described-bn",
                "language" => "bn",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Wrong Item Shipped",
                "slug" => "wrong-item-shipped-bn",
                "language" => "bn",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Damaged Item",
                "slug" => "damaged-item-bn",
                "language" => "bn",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Cancelled Order",
                "slug" => "cancelled-order-bn",
                "language" => "bn",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Late Delivery",
                "slug" => "late-delivery-bn",
                "language" => "bn",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Item Not Needed",
                "slug" => "item-not-needed-bn",
                "language" => "bn",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Changed Mind",
                "slug" => "changed-mind-bn",
                "language" => "bn",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                "name" => "Others",
                "slug" => "others-bn",
                "language" => "bn",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
        ];
    }

    // private function getEnglishDummyData(): array
    // {
    //     return [
    //         [
    //             "name" => "Product Not as Described",
    //             "slug" => "product-not-as-described",
    //             "language" => "en",
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //             'deleted_at' => null,
    //         ],
    //         [
    //             "name" => "Wrong Item Shipped",
    //             "slug" => "wrong-item-shipped",
    //             "language" => "en",
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //             'deleted_at' => null,
    //         ],
    //         [
    //             "name" => "Damaged Item",
    //             "slug" => "damaged-item",
    //             "language" => "en",
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //             'deleted_at' => null,
    //         ],
    //         [
    //             "name" => "Cancelled Order",
    //             "slug" => "cancelled-order",
    //             "language" => "en",
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //             'deleted_at' => null,
    //         ],
    //         [
    //             "name" => "Late Delivery",
    //             "slug" => "late-delivery",
    //             "language" => "en",
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //             'deleted_at' => null,
    //         ],
    //         [
    //             "name" => "Item Not Needed",
    //             "slug" => "item-not-needed",
    //             "language" => "en",
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //             'deleted_at' => null,
    //         ],
    //         [
    //             "name" => "Changed Mind",
    //             "slug" => "changed-mind",
    //             "language" => "en",
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //             'deleted_at' => null,
    //         ],
    //         [
    //             "name" => "Others",
    //             "slug" => "others",
    //             "language" => "en",
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //             'deleted_at' => null,
    //         ]

    //     ];
    // }
}
