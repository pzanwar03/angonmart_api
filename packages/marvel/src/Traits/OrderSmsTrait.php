<?php

namespace Marvel\Traits;

use Illuminate\Support\Facades\App;
use Marvel\Database\Models\Order;
use Marvel\Enums\EventType;

trait OrderSmsTrait
{
    use SmsTrait;
    
    public function sendOrderCancelSms(Order $order): void
    {
        $language = $order->language;
        App::setLocale($language);
        $customerName = $this->getCustomerName($order);
        $smsArray = [
            'order'             => $order,
            'language'          => $order->language ?? DEFAULT_LANGUAGE,
            'smsEventName'      => EventType::ORDER_CANCELLED,
            'adminMessage'      => __('sms.order.cancelOrder.admin.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number, 'customer_name' => $customerName]),
            'customerMessage'   => __('sms.order.cancelOrder.customer.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number, 'customer_name' => $customerName]),
            'storeOwnerMessage' => __('sms.order.cancelOrder.storeOwner.message'),
        ];
        $this->sendSmsOnOrderEvent($smsArray);
    }

    public function sendOrderCreationSms(Order $order): void
    {
        $language = $order->language;
        App::setLocale($language);
        $smsArray = [
            'order'             => $order,
            'language'          => $order->language ?? DEFAULT_LANGUAGE,
            'smsEventName'      => EventType::ORDER_CREATED,
            'adminMessage'      => __('sms.order.orderCreated.admin.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'customerMessage'   => __('sms.order.orderCreated.customer.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'storeOwnerMessage' => __('sms.order.orderCreated.storeOwner.message'),
        ];
        $this->sendSmsOnOrderEvent($smsArray);
    }

    /**
     * Always send order-creation SMS regardless of DB settings toggles:
     * - Customer receives a confirmation SMS if customer_contact is set.
     * - Each super-admin with a profile contact receives a new-order alert.
     * - Merchant (MERCHANT_CONTACT) is notified if set and not already sent.
     */
    public function sendOrderCreationSmsAlways(Order $order): void
    {
        try {
            App::setLocale($order->language ?? DEFAULT_LANGUAGE);
            $gateway         = $this->getOtpGateway();
            $customerMessage = __('sms.order.orderCreated.customer.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]);
            $adminMessage    = __('sms.order.orderCreated.admin.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]);
            $sentContacts    = [];

            $customerContact = trim((string) $order->customer_contact);
            if ($customerContact !== '') {
                $gateway->sendSms($customerContact, $customerMessage);
                $sentContacts[$this->normalizeSmsContact($customerContact)] = true;
            }

            $admins = $this->adminList();
            $admins->loadMissing('profile');
            foreach ($admins as $admin) {
                $contact = trim((string) ($admin->profile?->contact ?? ''));
                if ($contact === '') {
                    continue;
                }
                $normalized = $this->normalizeSmsContact($contact);
                if (isset($sentContacts[$normalized])) {
                    continue;
                }
                $gateway->sendSms($contact, $adminMessage);
                $sentContacts[$normalized] = true;
            }

            $merchantContact = trim((string) config('shop.merchant_contact'));
            if ($merchantContact !== '') {
                $normalized = $this->normalizeSmsContact($merchantContact);
                if (!isset($sentContacts[$normalized])) {
                    $gateway->sendSms($merchantContact, $adminMessage);
                }
            }
        } catch (\Exception $e) {
            info('sendOrderCreationSmsAlways failed: ' . $e->getMessage());
        }
    }

    protected function normalizeSmsContact(string $contact): string
    {
        return preg_replace('/\D+/', '', $contact) ?: strtolower(trim($contact));
    }

    public function sendPaymentDoneSuccessfullySms(Order $order): void
    {
        $language = $order->language;
        App::setLocale($language);
        $smsArray = [
            'order'             => $order,
            'language'          => $order->language ?? DEFAULT_LANGUAGE,
            'smsEventName'      => EventType::ORDER_PAYMENT_SUCCESS,
            'adminMessage'      => __('sms.order.paymentSuccessOrder.admin.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'customerMessage'   => __('sms.order.paymentSuccessOrder.customer.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'storeOwnerMessage' => __('sms.order.paymentSuccessOrder.storeOwner.message'),
        ];
        $this->sendSmsOnOrderEvent($smsArray);
    }

    public function sendOrderStatusChangeSms(Order $order): void
    {
        $language = $order->language;
        App::setLocale($language);
        $status = ucfirst(str_replace('-', ' ', $order->order_status));
        $smsArray = [
            'order'             => $order,
            'language'          => $order->language ?? DEFAULT_LANGUAGE,
            'smsEventName'      => EventType::ORDER_STATUS_CHANGED,
            'adminMessage'      => __('sms.order.statusChangeOrder.admin.message', [
                'ORDER_TRACKING_NUMBER' => $order->tracking_number,
                'order_status'          => $status
            ]),
            'customerMessage'   => __('sms.order.statusChangeOrder.customer.message', [
                'ORDER_TRACKING_NUMBER' => $order->tracking_number,
                'order_status'          => $status
            ]),
            'storeOwnerMessage' => __('sms.order.statusChangeOrder.storeOwner.message', ['order_status' => $status]),
        ];
        $this->sendSmsOnOrderEvent($smsArray, false);
    }

    public function sendOrderDeliveredSms($order): void
    {
        $language = $order->language;
        App::setLocale($language);
        $smsArray = [
            'order'             => $order,
            'language'          => $order->language ?? DEFAULT_LANGUAGE,
            'smsEventName'      => EventType::ORDER_DELIVERED,
            'adminMessage'      => __('sms.order.deliverOrder.admin.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'customerMessage'   => __('sms.order.deliverOrder.customer.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'storeOwnerMessage' => __('sms.order.deliverOrder.storeOwner.message'),
        ];
        $this->sendSmsOnOrderEvent($smsArray, false);
    }


    public function sendPaymentFailedSms($order): void
    {
        $language = $order->language;
        App::setLocale($language);
        $smsArray = [
            'order'             => $order,
            'language'          => $order->language ?? DEFAULT_LANGUAGE,
            'smsEventName'      => EventType::ORDER_PAYMENT_FAILED,
            'adminMessage'      => __('sms.order.paymentFailedOrder.admin.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'customerMessage'   => __('sms.order.paymentFailedOrder.customer.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'storeOwnerMessage' => __('sms.order.paymentFailedOrder.storeOwner.message'),
        ];
        $this->sendSmsOnOrderEvent($smsArray, false);
    }
    protected function getCustomerName($order)
    {
        $customerName = $order->customer;
        if (!$customerName) {
            $customerName = "Guest Customer";
        } else {
            $customerName = $order->customer->name;
        }
        return $customerName;
    }
    public function sendRefundRequestedSms($refund): void
    {
        $order = $refund->order;
        $language = $order->language;
        App::setLocale($language);
        $smsArray = [
            'order'             => $order,
            'language'          => $order->language ?? DEFAULT_LANGUAGE,
            'smsEventName'      => EventType::ORDER_REFUND,
            'adminMessage'      => __('sms.order.refundRequested.admin.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
            'customerMessage'   => __('sms.order.refundRequested.customer.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]),
        ];
        $this->sendSmsOnRefund($smsArray);
    }
    public function sendRefundUpdateSms($refund): void
    {
        $order = $refund->order;
        $language = $order->language;
        App::setLocale($language);
        $smsArray = [
            'order'             => $order,
            'language'          => $order->language ?? DEFAULT_LANGUAGE,
            'smsEventName'      => EventType::ORDER_REFUND,
            'adminMessage'      => __('sms.order.refundUpdated.admin.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number, ':refund_status' => $refund->status]),
            'customerMessage'   => __('sms.order.refundUpdated.customer.message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number, ':refund_status' => $refund->status]),
        ];
        $this->sendSmsOnRefund($smsArray);
    }
}
