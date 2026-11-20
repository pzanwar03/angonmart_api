<?php

namespace Marvel\Database\Repositories;

use Marvel\Database\Models\Client;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class ClientRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
        'phone' => 'like',
        'email' => 'like',
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
        return Client::class;
    }
}
