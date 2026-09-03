<?php

namespace Marvel\Database\Repositories;

use Marvel\Database\Models\DeliverySchedule;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class DeliveryScheduleRepository extends BaseRepository
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
        return DeliverySchedule::class;
    }

    public function storeSchedule(array $data): DeliverySchedule
    {
        if (!empty($data['is_default'])) {
            DeliverySchedule::withoutGlobalScopes()->update(['is_default' => false]);
        }

        return $this->create($data);
    }

    public function updateSchedule(int $id, array $data): DeliverySchedule
    {
        $schedule = $this->findOrFail($id);

        if (!empty($data['is_default'])) {
            DeliverySchedule::withoutGlobalScopes()
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $schedule->update($data);

        return $schedule->fresh();
    }
}
