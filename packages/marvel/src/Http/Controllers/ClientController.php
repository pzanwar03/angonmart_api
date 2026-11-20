<?php

namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        return $this->repository->listClients();
    }

    public function store(CreateClientRequest $request)
    {
        return $this->repository->storeClient($request->validated());
    }

    public function show($id)
    {
        try {
            return $this->repository->decorate($this->repository->findClient($id));
        } catch (ModelNotFoundException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    public function update(UpdateClientRequest $request, $id)
    {
        try {
            return $this->repository->updateClient($id, $request->validated());
        } catch (ModelNotFoundException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    public function destroy($id)
    {
        try {
            return $this->repository->deleteClient($id);
        } catch (ModelNotFoundException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }
}
