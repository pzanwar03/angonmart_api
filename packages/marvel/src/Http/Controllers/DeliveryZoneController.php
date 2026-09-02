<?php

namespace Marvel\Http\Controllers;

use Illuminate\Http\Request;
use Marvel\Database\Repositories\DeliveryZoneRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\CreateDeliveryZoneRequest;
use Marvel\Http\Requests\UpdateDeliveryZoneRequest;

class DeliveryZoneController extends CoreController
{
    public $repository;

    public function __construct(DeliveryZoneRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->decorateAreas($this->repository->with('areas')->all());
    }

    public function store(CreateDeliveryZoneRequest $request)
    {
        try {
            return $this->repository->decorateAreas($this->repository->storeZone($request->validated()));
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    public function show($id)
    {
        try {
            return $this->repository->decorateAreas($this->repository->with('areas')->findOrFail($id));
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    public function update(UpdateDeliveryZoneRequest $request, $id)
    {
        try {
            return $this->repository->decorateAreas($this->repository->updateZone((int) $id, $request->validated()));
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
