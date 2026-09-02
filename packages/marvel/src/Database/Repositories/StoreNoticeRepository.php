<?php


namespace Marvel\Database\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Marvel\Database\Models\StoreNotice;
use Marvel\Database\Models\User;
use Marvel\Enums\Permission;
use Marvel\Enums\StoreNoticeType;
use Marvel\Events\StoreNoticeEvent;
use Marvel\Exceptions\MarvelException;
use Marvel\Traits\StoreNoticeable;
use Mpdf\Container\NotFoundException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreNoticeRepository extends BaseRepository
{
    use StoreNoticeable;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'notice'       => 'like',
        'effective_from',
        'expired_at',
        'type',
        'receiver.id',
        'users.id',
        'creator_role' => 'like',
    ];

    /**
     * @var array
     */
    protected $dataArray = [
        'priority',
        'notice',
        'description',
        'effective_from',
        'expired_at',
        'type',
    ];


    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return StoreNotice::class;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws MarvelException
     */
    public function fetchStoreNotices(Request $request): mixed
    {
        try {
            $storeNotices = $this->where('id', '!=', null);

            /* Guests only see general site notices */
            if (!$request->user()) {
                return $storeNotices->whereDate('expired_at', '>=', now());
            }

            if (!$request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
                /* Non super-admin users only see notices addressed to them or created by them */
                $storeNotices->where('created_by', $request->user()->id)
                    ->orWhereRelation('users', 'id', $request->user()->id);
            }

            return $storeNotices->whereDate('expired_at', '>=', now());
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * @param Request $request
     * @return array[]
     */
    public function fetchStoreNoticeType(Request $request)
    {
        if ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
            $typeArr = [
                ['name' => "ALL VENDOR", 'value' => StoreNoticeType::ALL_VENDOR],
                ['name' => "SPECIFIC VENDOR", 'value' => StoreNoticeType::SPECIFIC_VENDOR]
            ];
            return $typeArr;
        }
        $typeArr = [
            ['name' => "ALL SHOP", 'value' => StoreNoticeType::ALL_SHOP],
            ['name' => "SPECIFIC SHOP", 'value' => StoreNoticeType::SPECIFIC_SHOP]
        ];
        return $typeArr;
    }

    /**
     * This method will generate User list or Shop list based on requested user permission
     * @param Request $request
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Response
     * @throws MarvelException
     */
    public function fetchUserToSendNotification(Request $request)
    {
        try {
            if ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
                return User::permission(Permission::STAFF)->orderBy('name')->get();
            }
            return collect();
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * It creates a new store notice, syncs the users and shops, and syncs the read status.
     *
     * @param Request request The request object
     *
     * @return StoreNotice storeNotice is being returned.
     */
    public function saveStoreNotice(Request $request)
    {
        try {
            $storeNotice = $this->create($request->only($this->dataArray));
            $this->syncUsersOrShops($request, $storeNotice);
            $this->syncReadStatus($storeNotice);
            event(new StoreNoticeEvent($storeNotice, 'create', $request->user()));
            return $storeNotice;
        } catch (Exception $e) {
            throw new HttpException(400, COULD_NOT_CREATE_THE_RESOURCE);
        }
    }

    /**
     * Updating Specific resource in storage
     *
     * @param \Marvel\Database\Models\StoreNotice $storeNotice
     * @param array $data
     * @return mixed
     */
    public function updateStoreNotice(Request $request, StoreNotice $storeNotice)
    {

        try {
            $storeNotice->update($request->only($this->dataArray));
            $this->syncUsersOrShops($request, $storeNotice);
            $this->syncReadStatus($storeNotice);
            event(new StoreNoticeEvent($storeNotice, 'update', $request->user()));
            return $storeNotice;
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG);
        }
    }
}
