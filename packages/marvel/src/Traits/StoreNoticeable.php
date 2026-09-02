<?php

namespace Marvel\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Marvel\Database\Models\StoreNotice;
use Marvel\Database\Models\User;
use Marvel\Enums\Permission;
use Marvel\Enums\StoreNoticeType;

trait StoreNoticeable
{

    /**
     * this method will sync read_status of StoreNotice
     *
     * @param StoreNotice $storeNotice
     * @return array | null
     */
    public function syncReadStatus(StoreNotice $storeNotice)
    {
        $userIdArr = match ($storeNotice->type) {
            StoreNoticeType::ALL_VENDOR, StoreNoticeType::ALL_SHOP => User::permission(Permission::STAFF)->get()->pluck('id'),
            StoreNoticeType::SPECIFIC_SHOP, StoreNoticeType::SPECIFIC_VENDOR => $storeNotice->users()->pluck('id'),
        };
        $storeNoticeReadArray =  Arr::map(
            $userIdArr->toArray(),
            fn ($uId) => [
                "store_notice_id" => $storeNotice->id,
                "user_id"         => $uId,
                "is_read"         => $uId === $storeNotice->created_by
            ]
        );
        return $storeNotice->read_status()->sync($storeNoticeReadArray);
    }

    /**
     * This method will attach Users or Shops to StoreNotice
     *
     * @param mixed $request
     * @param StoreNotice $storeNotice
     * @return StoreNotice $storeNotice
     */
    protected function syncUsersOrShops(Request $request, StoreNotice $storeNotice)
    {
        switch ($request->type) {
            case StoreNoticeType::ALL_VENDOR:
            case StoreNoticeType::ALL_SHOP:
                $request->received_by = User::permission(Permission::STAFF)->pluck('id');
                $storeNotice->users()->sync($request->received_by);
                break;
            case StoreNoticeType::SPECIFIC_VENDOR:
            case StoreNoticeType::SPECIFIC_SHOP:
                $storeNotice->users()->sync($request->received_by);
                break;
        }
        return $storeNotice;
    }
}
