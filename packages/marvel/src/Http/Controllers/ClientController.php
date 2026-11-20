<?php

namespace Marvel\Http\Controllers;

use Illuminate\Http\Request;
use Marvel\Database\Repositories\ClientRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\CreateClientRequest;
use Marvel\Http\Requests\UpdateClientRequest;

class ClientController extends CoreController
{
    public $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->all();
    }

    public function store(CreateClientRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    public function show($id)
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    public function update(UpdateClientRequest $request, $id)
    {
        try {
            $client = $this->repository->findOrFail($id);
            $client->update($request->validated());

            return $client->fresh();
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
