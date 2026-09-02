<?php


namespace Marvel\Database\Repositories;

use Exception;
use Marvel\Database\Models\Address;
use Marvel\Database\Models\Order;
use Marvel\Database\Models\Refund;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Enums\Permission;
use Marvel\Enums\RefundStatus;
use Marvel\Exceptions\MarvelException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class RefundRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'order_id',
        'description',
        'refund_policy_id',
        'refund_policy.slug',
        'refund_reason.slug',
    ];

    protected $dataArray = [
        'order_id',
        'images',
        'title',
        'description',
        'refund_policy_id',
        'refund_reason_id'
    ];
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Refund::class;
    }

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
        }
    }

    public function storeRefund($request)
    {
        $user = $request->user();
        $refunds = $this->where('order_id', $request->order_id)->get();
        if (count($refunds)) {
            throw new MarvelException(ORDER_ALREADY_HAS_REFUND_REQUEST);
        }
        try {
            $order = Order::findOrFail($request->order_id);
        } catch (Exception $th) {
            throw new MarvelException(NOT_FOUND);
        }
        if ($user->id !== $order->customer_id || $user->hasPermissionTo(Permission::SUPER_ADMIN)) {
            throw new MarvelException(NOT_AUTHORIZED);
        }
        $data = $request->only($this->dataArray);
        $data['customer_id'] = $order->customer_id;
        $data['amount'] = $order->amount;
        $refund = $this->create($data);
        return $this->find($refund->id);
    }

    public function updateRefund($request, $refund)
    {
        $data = $request->only(['status']);
        $refund->update($data);

        if ($refund['status'] == RefundStatus::APPROVED) {
            $orderData['order_status'] = OrderStatus::REFUNDED;
            $orderData['payment_status'] = PaymentStatus::REFUNDED;
            $this->changeOrderStatus($refund->order_id, $orderData);
        }
        return $refund;
    }

    private function changeOrderStatus($orderId, array $data)
    {
        $order = Order::findOrFail($orderId);
        $order->update($data);
    }
}
