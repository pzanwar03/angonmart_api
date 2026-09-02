<?php

namespace Marvel\Traits;

use Marvel\Database\Models\Order;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Events\OrderCancelled;
use Marvel\Events\OrderDelivered;
use Marvel\Events\OrderStatusChanged;
use Marvel\Events\PaymentFailed;
use Marvel\Events\PaymentSuccess;

trait OrderStatusManagerWithPaymentTrait
{

    /**
     * orderStatusManagementOnPayment
     *
     * @param  mixed $order
     * @param  mixed $order_status
     * @param  mixed $payment_status
     * @return void
     */
    public function orderStatusManagementOnPayment($order, $order_status, $payment_status)
    {

        switch ($payment_status) {
            case PaymentStatus::SUCCESS:
                event(new PaymentSuccess($order));
                break;
            case PaymentStatus::FAILED:
                event(new PaymentFailed($order));
                break;
            case PaymentStatus::REVERSAL:
                event(new PaymentFailed($order));
                break;
            case PaymentStatus::PENDING:
                # code...
                # send notification to user about order is pending.
                break;
            case PaymentStatus::PROCESSING:
                # code...
                # send notification to user about order is processing.
                break;

            case PaymentStatus::AWAITING_FOR_APPROVAL:
                # code...
                # send notification to user about order is pending & payment is waiting for approval.
                break;
        }
        $this->fireEventOnOrderStatus($order, $order_status);
    }

    /**
     * orderStatusManagementOnCOD
     *
     * @param  mixed $order
     * @param  string $prev_status
     * @param  string $new_status
     * @return void
     */
    public function orderStatusManagementOnCOD($order, $prev_status, $new_status)
    {
        switch ($new_status) {
            case OrderStatus::CANCELLED:
                # code...
                $this->orderStatusManagementOnCancelled($order);
                event(new OrderCancelled($order));
                break;

            case OrderStatus::REFUNDED:
                # code...
                event(new OrderCancelled($order));
                break;

            case OrderStatus::FAILED:
                # code...
                break;
            case OrderStatus::PROCESSING:
                # do nothing
                # this event already has been fired from OrderRepository
                break;
            default:
                event(new OrderStatusChanged($order));
                break;
        }
    }


    public function fireEventOnOrderStatus($order, $currentStatus)
    {
        switch ($currentStatus) {
            case OrderStatus::CANCELLED:
                # code...
                $this->orderStatusManagementOnCancelled($order);
                event(new OrderCancelled($order));
                break;

            case OrderStatus::REFUNDED:
                $this->orderStatusManagementOnCancelled($order);
                event(new OrderCancelled($order));
                break;

            case OrderStatus::FAILED:
                $this->orderStatusManagementOnCancelled($order);
                event(new OrderCancelled($order));
                break;

            default:
                event(new OrderStatusChanged($order));
                break;
        }
    }

    /**
     * orderAlreadyExists
     *
     * @param  mixed $order
     * @param  string $tracking_number
     * @return bool
     */
    public function orderAlreadyExists($tracking_number)
    {
        try {
            $order_exists = false;
            $order_exists = Order::where('tracking_number', '=', $tracking_number)->exists();
            if ($order_exists) {
                return true;
            }
            return $order_exists;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * orderStatusManagementOnCancelled
     *
     * @param  mixed $order
     * @return void
     */
    public function orderStatusManagementOnCancelled($order)
    {
        $order->cancelled_amount += $order->paid_total;
        $order->cancelled_tax += $order->sales_tax;
        $order->cancelled_delivery_fee = $order->delivery_fee;
        $order->sales_tax = 0;
        $order->delivery_fee = 0;
        $order->paid_total = 0;
        $order->total = 0;
        $order->save();
        //TODO: give refund to customer if order is pre paid
    }


    /**
     * The function checks if the order status is one of the final statuses.
     *
     * @param Order order The parameter "order" is an instance of the Order class.
     *
     * @return bool a boolean value, indicating whether the order status is final or not.
     */
    public function checkOrderStatusIsFinal(Order $order): bool
    {
        $orderStatuses = [OrderStatus::COMPLETED, OrderStatus::CANCELLED, OrderStatus::REFUNDED];
        return in_array($order->order_status, $orderStatuses);
    }
}
