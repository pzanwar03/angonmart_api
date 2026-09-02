<?php

namespace Marvel\Traits;

use Marvel\Enums\PaymentStatus;
use Marvel\Enums\PaymentGatewayType;

trait OrderManagementTrait
{
    use OrderStatusManagerWithPaymentTrait;

    /**
     * changeOrderStatus
     *
     * @param  mixed $order
     * @param  mixed $status
     * @return void
     */
    public function changeOrderStatus($order, $status)
    {
        $prev_order_status = $order->order_status;
        $order->order_status = $status;
        $new_order_status = $order->order_status;

        if ($prev_order_status !== $new_order_status) {
            $payment_gateway_type = isset($order->payment_gateway) ? $order->payment_gateway : PaymentGatewayType::CASH_ON_DELIVERY;
            $usedPaymentGateway = !in_array($payment_gateway_type, [PaymentGatewayType::CASH, PaymentGatewayType::CASH_ON_DELIVERY]);
            $isPaymentSuccess = $order->payment_status === PaymentStatus::SUCCESS;
            if ($usedPaymentGateway) {
                if ($isPaymentSuccess) {
                    $this->orderStatusManagementOnPayment($order, $new_order_status, '');
                } else {
                    $this->orderStatusManagementOnPayment($order, $new_order_status, $order->payment_status);
                }
            } else {
                $this->orderStatusManagementOnCOD($order, $prev_order_status, $new_order_status);
            }
        }
        $order->save();

        return $order;
    }
}
