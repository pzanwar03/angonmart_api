<?php

namespace Marvel\Database\Repositories;

use Illuminate\Support\Arr;
use Marvel\Database\Models\BdDistrict;
use Marvel\Database\Models\BdDivision;
use Marvel\Database\Models\BdThana;
use Marvel\Database\Models\DeliveryZone;
use Marvel\Database\Models\DeliveryZoneArea;
use Marvel\Database\Models\DeliveryZoneScheduleCharge;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class DeliveryZoneRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }

    public function model()
    {
        return DeliveryZone::class;
    }

    public function decorateAreas($zones)
    {
        $collection = $zones instanceof DeliveryZone ? collect([$zones]) : collect($zones);
        $grouped = [
            'division' => [],
            'district' => [],
            'thana' => [],
        ];

        foreach ($collection as $zone) {
            foreach ($zone->areas ?? [] as $area) {
                $grouped[$area->location_type][] = $area->location_id;
            }
        }

        $names = [
            'division' => BdDivision::query()->whereIn('id', $grouped['division'] ?: [0])->pluck('name', 'id'),
            'district' => BdDistrict::query()->whereIn('id', $grouped['district'] ?: [0])->pluck('name', 'id'),
            'thana' => BdThana::query()->whereIn('id', $grouped['thana'] ?: [0])->pluck('name', 'id'),
        ];

        foreach ($collection as $zone) {
            foreach ($zone->areas ?? [] as $area) {
                $area->location_name = $names[$area->location_type][$area->location_id] ?? null;
            }
        }

        return $zones;
    }

    public function storeZone(array $data): DeliveryZone
    {
        $areas = $data['areas'] ?? [];
        unset($data['areas']);

        if (!empty($data['is_default'])) {
            DeliveryZone::query()->update(['is_default' => false]);
        }

        $charges = $data['charges'] ?? [];
        unset($data['charges']);

        $zone = $this->create($data);
        $this->syncAreas($zone, $areas);
        $this->syncCharges($zone, $charges);

        return $zone->load(['areas', 'charges']);
    }

    public function updateZone(int $id, array $data): DeliveryZone
    {
        $zone = $this->findOrFail($id);
        $areas = $data['areas'] ?? null;
        $charges = $data['charges'] ?? null;
        unset($data['areas'], $data['charges']);

        if (!empty($data['is_default'])) {
            DeliveryZone::query()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $zone->update($data);

        if (is_array($areas)) {
            $this->syncAreas($zone, $areas);
        }
        if (is_array($charges)) {
            $this->syncCharges($zone, $charges);
        }

        return $zone->fresh(['areas', 'charges']);
    }

    /**
     * Resolve the matching delivery zone for a shipping address.
     * Most specific assignment wins: thana → district → division → default zone.
     */
    public function resolveZone($shippingAddress): ?DeliveryZone
    {
        $address = $this->normalizeAddress($shippingAddress);
        if (empty($address)) {
            return $this->defaultZone();
        }

        $thanaId = $address['thana_id'] ?? null;
        $districtId = $address['district_id'] ?? null;
        $divisionId = $address['division_id'] ?? null;

        if (!$districtId && $thanaId) {
            $districtId = BdThana::query()->where('id', $thanaId)->value('district_id');
        }
        if (!$divisionId && $districtId) {
            $divisionId = BdDistrict::query()->where('id', $districtId)->value('division_id');
        }

        $lookups = [];
        if ($thanaId) {
            $lookups[] = ['location_type' => 'thana', 'location_id' => $thanaId];
        }
        if ($districtId) {
            $lookups[] = ['location_type' => 'district', 'location_id' => $districtId];
        }
        if ($divisionId) {
            $lookups[] = ['location_type' => 'division', 'location_id' => $divisionId];
        }

        foreach ($lookups as $lookup) {
            $area = DeliveryZoneArea::query()
                ->where($lookup)
                ->whereHas('zone', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('zone')
                ->first();

            if ($area && $area->zone) {
                return $area->zone;
            }
        }

        return $this->defaultZone();
    }

    /**
     * Resolve the delivery charge for a shipping address and optional schedule.
     * Matrix charge (zone + schedule) wins; otherwise the zone fallback charge.
     */
    public function resolveCharge($shippingAddress, $scheduleId = null): float
    {
        $zone = $this->resolveZone($shippingAddress);
        if (!$zone) {
            return 0;
        }

        if ($scheduleId) {
            $matrix = DeliveryZoneScheduleCharge::query()
                ->where('delivery_zone_id', $zone->id)
                ->where('delivery_schedule_id', $scheduleId)
                ->first();
            if ($matrix) {
                return (float) $matrix->charge;
            }
        }

        return (float) $zone->charge;
    }

    protected function defaultZone(): ?DeliveryZone
    {
        return DeliveryZone::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    protected function defaultCharge(): float
    {
        $default = $this->defaultZone();

        return $default ? (float) $default->charge : 0;
    }

    protected function syncCharges(DeliveryZone $zone, array $charges): void
    {
        $normalized = collect($charges)
            ->filter(fn ($charge) => !empty($charge['delivery_schedule_id']))
            ->map(fn ($charge) => [
                'delivery_schedule_id' => (int) $charge['delivery_schedule_id'],
                'charge' => (float) ($charge['charge'] ?? 0),
            ])
            ->unique('delivery_schedule_id')
            ->values();

        $keepIds = $normalized->pluck('delivery_schedule_id')->all();
        $zone->charges()
            ->when(
                !empty($keepIds),
                fn ($query) => $query->whereNotIn('delivery_schedule_id', $keepIds),
                fn ($query) => $query
            )
            ->delete();

        foreach ($normalized as $row) {
            $zone->charges()->updateOrCreate(
                ['delivery_schedule_id' => $row['delivery_schedule_id']],
                ['charge' => $row['charge']]
            );
        }
    }

    protected function syncAreas(DeliveryZone $zone, array $areas): void
    {
        $normalized = collect($areas)
            ->filter(fn ($area) => !empty($area['location_type']) && !empty($area['location_id']))
            ->map(fn ($area) => [
                'location_type' => $area['location_type'],
                'location_id' => (int) $area['location_id'],
            ])
            ->unique(fn ($area) => $area['location_type'] . ':' . $area['location_id'])
            ->values();

        if ($normalized->isEmpty()) {
            $zone->areas()->delete();
            return;
        }

        DeliveryZoneArea::query()
            ->where(function ($query) use ($normalized) {
                foreach ($normalized as $area) {
                    $query->orWhere(function ($inner) use ($area) {
                        $inner->where('location_type', $area['location_type'])
                            ->where('location_id', $area['location_id']);
                    });
                }
            })
            ->where('delivery_zone_id', '!=', $zone->id)
            ->delete();

        $keepKeys = $normalized->map(fn ($area) => $area['location_type'] . ':' . $area['location_id'])->all();
        $zone->load('areas');
        $zone->areas->each(function (DeliveryZoneArea $area) use ($keepKeys) {
            if (!in_array($area->location_type . ':' . $area->location_id, $keepKeys, true)) {
                $area->delete();
            }
        });

        foreach ($normalized as $area) {
            $zone->areas()->updateOrCreate(
                [
                    'location_type' => $area['location_type'],
                    'location_id' => $area['location_id'],
                ],
                []
            );
        }
    }

    protected function normalizeAddress($shippingAddress): array
    {
        if (is_object($shippingAddress) && method_exists($shippingAddress, 'all')) {
            $shippingAddress = $shippingAddress->all();
        }
        if (is_object($shippingAddress)) {
            $shippingAddress = (array) $shippingAddress;
        }
        if (!is_array($shippingAddress)) {
            return [];
        }

        return Arr::only($shippingAddress, [
            'thana_id',
            'district_id',
            'division_id',
            'country',
        ]);
    }
}
