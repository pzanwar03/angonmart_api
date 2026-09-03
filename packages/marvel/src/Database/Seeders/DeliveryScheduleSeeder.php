<?php

namespace Marvel\Database\Seeders;

use Illuminate\Database\Seeder;
use Marvel\Database\Models\BdDistrict;
use Marvel\Database\Models\DeliverySchedule;
use Marvel\Database\Models\DeliveryZone;

class DeliveryScheduleSeeder extends Seeder
{
    public function run()
    {
        $regular = DeliverySchedule::query()->firstOrCreate(
            ['name' => 'Regular Delivery'],
            [
                'description' => '2-3 days delivery',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ]
        );

        $express = DeliverySchedule::query()->firstOrCreate(
            ['name' => 'Express Delivery'],
            [
                'description' => 'Same day delivery',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ]
        );

        if ($regular->is_default) {
            DeliverySchedule::query()
                ->where('id', '!=', $regular->id)
                ->update(['is_default' => false]);
        }

        $insideDhaka = DeliveryZone::query()->firstOrCreate(
            ['name' => 'Inside Dhaka'],
            [
                'charge' => 60,
                'is_active' => true,
                'is_default' => false,
            ]
        );

        $outsideDhaka = DeliveryZone::query()->firstOrCreate(
            ['name' => 'Outside Dhaka'],
            [
                'charge' => 120,
                'is_active' => true,
                'is_default' => true,
            ]
        );

        if ($outsideDhaka->is_default) {
            DeliveryZone::query()
                ->where('id', '!=', $outsideDhaka->id)
                ->update(['is_default' => false]);
        }

        $dhakaDistrictId = BdDistrict::query()->where('name', 'Dhaka')->value('id');
        if ($dhakaDistrictId && $insideDhaka->areas()->count() === 0) {
            $insideDhaka->areas()->create([
                'location_type' => 'district',
                'location_id' => $dhakaDistrictId,
            ]);
        }

        $matrix = [
            [$insideDhaka, $regular, 60],
            [$insideDhaka, $express, 120],
            [$outsideDhaka, $regular, 120],
            [$outsideDhaka, $express, 200],
        ];

        foreach ($matrix as [$zone, $schedule, $charge]) {
            $zone->charges()->updateOrCreate(
                ['delivery_schedule_id' => $schedule->id],
                ['charge' => $charge]
            );
        }
    }
}
