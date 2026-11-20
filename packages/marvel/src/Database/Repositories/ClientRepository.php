<?php

namespace Marvel\Database\Repositories;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Marvel\Database\Models\User;
use Marvel\Enums\Permission;
use Marvel\Enums\Role;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class ClientRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
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
        return User::class;
    }

    public function listClients()
    {
        $this->applyClientScope();

        return $this->decorate($this->all());
    }

    public function findClient($id): User
    {
        $this->applyClientScope();

        return $this->find($id);
    }

    protected function applyClientScope(): void
    {
        $this->with(['profile', 'address']);
        $this->scopeQuery(function ($query) {
            return $query->permission(Permission::CLIENT);
        });
    }

    public function storeClient(array $data): User
    {
        $user = $this->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(16)),
        ]);

        $user->givePermissionTo(Permission::CLIENT);
        $user->assignRole(Role::CLIENT);

        $this->syncPhone($user, $data['phone'] ?? null);
        $this->syncAddress($user, $data['address'] ?? null);

        return $this->decorate($user->fresh(['profile', 'address']));
    }

    public function updateClient($id, array $data): User
    {
        $user = $this->findClient($id);
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $this->syncPhone($user, $data['phone'] ?? null);
        $this->syncAddress($user, $data['address'] ?? null);

        return $this->decorate($user->fresh(['profile', 'address']));
    }

    public function deleteClient($id)
    {
        $user = $this->findClient($id);
        $user->address()->delete();
        $user->profile()->delete();

        return $user->delete();
    }

    public function decorate($users)
    {
        $collection = $users instanceof User ? collect([$users]) : collect($users);

        $collection->each(function (User $user) {
            $address = $user->address->first();
            $payload = $address ? $address->address : null;

            $user->setAttribute('phone', optional($user->profile)->contact);
            $user->setAttribute(
                'address',
                is_array($payload) ? ($payload['street_address'] ?? null) : null
            );
            $user->syncOriginal();
            $user->unsetRelation('profile');
            $user->unsetRelation('address');
        });

        return $users;
    }

    protected function syncPhone(User $user, $phone): void
    {
        $contact = $phone === '' ? null : $phone;
        $profile = $user->profile;

        if ($profile) {
            $profile->update(['contact' => $contact]);
            return;
        }

        if ($contact !== null) {
            $user->profile()->create(['contact' => $contact]);
        }
    }

    protected function syncAddress(User $user, $address): void
    {
        $street = $address === '' ? null : $address;
        $existing = $user->address()->first();

        if ($street === null) {
            if ($existing) {
                $existing->delete();
            }
            return;
        }

        $payload = ['street_address' => $street];

        if ($existing) {
            $existing->update(['address' => $payload]);
            return;
        }

        $user->address()->create([
            'title' => 'Primary',
            'type' => 'billing',
            'default' => true,
            'address' => $payload,
        ]);
    }
}
