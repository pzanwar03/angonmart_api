<?php

namespace Marvel\Http\Controllers;

use Illuminate\Http\Request;
use Marvel\Database\Models\DeliverySchedule;
use Marvel\Database\Repositories\DeliveryScheduleRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\CreateDeliveryScheduleRequest;
use Marvel\Http\Requests\UpdateDeliveryScheduleRequest;

class DeliveryScheduleController extends CoreController
{
    public $repository;

    public function __construct(DeliveryScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $query = DeliverySchedule::query();
        $user = $request->user() ?? $request->user('sanctum');

        if (!$user || !$request->boolean('all')) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function store(CreateDeliveryScheduleRequest $request)
    {
        try {
            return $this->repository->storeSchedule($request->validated());
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    public function show($id)
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    public function update(UpdateDeliveryScheduleRequest $request, $id)
    {
        try {
            return $this->repository->updateSchedule((int) $id, $request->validated());
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    public function destroy($id)
    {
        try {
            return $this->repository->findOrFail($id)->delete();
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }
}
