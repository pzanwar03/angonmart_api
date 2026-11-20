<!DOCTYPE html>
<html lang="{{ $language ?? 'en' }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Order Invoice</title>
    <style type="text/css">
        @page {
            margin: 18px 20px;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #000000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
        }
    </style>
    @if (($language ?? '') === 'bn')
        <style type="text/css">
            body {
                font-family: DejaVu Sans, Arial, sans-serif, bangla;
            }
        </style>
    @endif
</head>

<body>
    @php
        $options = $settings->options ?? [];
        $authorDetails = $options['contactDetails'] ?? [];
        $authorLocation = $authorDetails['location'] ?? [];
        $customer = $order->customer;
        $shippingAddress = $order->shipping_address ?? [];
        $products = $order->products;
        $currency = $options['currency'] ?? 'USD';
        $currencyOptions = $options['currencyOptions'] ?? ['formation' => 'en-US', 'fractions' => 2];
        $locale = $currencyOptions['formation'] ?? 'en-US';

        $amountDue = $order->payment_status !== 'payment-success' ? $order->paid_total - intval($order?->wallet_point?->amount) : 0;
        if ($order->order_status === 'order-completed') {
            $amountDue = 0;
        }

        $cancelled_products = [];

        $logoPath = public_path('logo.png');
        $logoSrc = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $label = function ($key, $fallback) use ($translated_text) {
            return $translated_text[$key] ?? $fallback;
        };

        $money = function ($amount) use ($currency, $locale) {
            return formatCurrency((float) ($amount ?? 0), $currency, $locale);
        };

        $addressParts = array_filter([
            $shippingAddress['street_address'] ?? null,
            $shippingAddress['city'] ?? null,
            $shippingAddress['state'] ?? null,
            $shippingAddress['zip'] ?? null,
            $shippingAddress['country'] ?? null,
        ]);

        $invoiceDate = $order->created_at ? $order->created_at->format('j M Y') : date('j M Y');
        $orderStatus = ucwords(str_replace(['order-', '-'], ['', ' '], (string) $order->order_status));
        $paymentStatus = ucwords(str_replace(['payment-', '-'], ['', ' '], (string) $order->payment_status));
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-collapse:collapse;">
        <tr>
            <td style="padding:0;">
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-collapse:collapse;">
                    <tr>
                        <td style="background-color:#000000; height:6px; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:18px 22px 8px 22px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="58%" valign="middle">
                                        @if ($logoSrc)
                                            <img src="{{ $logoSrc }}" alt="{{ $options['siteTitle'] ?? 'Angon Mart' }}" style="height:58px; width:auto;">
                                        @else
                                            <div style="font-size:22px; font-weight:bold; color:#000000;">{{ $options['siteTitle'] ?? 'Angon Mart' }}</div>
                                        @endif
                                    </td>
                                    <td width="42%" valign="middle" align="right">
                                        <div style="font-size:22px; font-weight:bold; letter-spacing:1px; color:#000000;">
                                            {{ ($language ?? '') === 'bn' ? 'ইনভয়েস' : 'INVOICE' }}
                                        </div>
                                        <div style="margin-top:4px; font-size:13px; color:#000000;">
                                            {{ $label('invoice_no', 'Invoice no') }}: {{ $order->tracking_number }}
                                        </div>
                                        <div style="margin-top:2px; font-size:11px; color:#000000;">
                                            {{ $label('date', 'Date') }}: {{ $invoiceDate }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 22px 0 22px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="49%" valign="top" style="background-color:#ffffff; border:1px solid #000000; padding:12px 14px;">
                                        <div style="font-size:10px; letter-spacing:0.6px; color:#000000; font-weight:bold; text-transform:uppercase;">
                                            {{ ($language ?? '') === 'bn' ? 'বিল প্রাপক' : 'Billed to' }}
                                        </div>
                                        @if (!empty($customer['name']))
                                            <div style="margin-top:6px; font-size:14px; font-weight:bold; color:#000000;">{{ $customer['name'] }}</div>
                                        @endif
                                        @if (!empty($customer['email']))
                                            <div style="margin-top:3px; color:#000000;">{{ $customer['email'] }}</div>
                                        @endif
                                        @if (!empty($order->customer_contact))
                                            <div style="margin-top:2px; color:#000000;">{{ $order->customer_contact }}</div>
                                        @endif
                                        @if (!empty($addressParts))
                                            <div style="margin-top:6px; color:#000000; line-height:1.45;">
                                                {{ implode(', ', $addressParts) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td width="2%"></td>
                                    <td width="49%" valign="top" style="background-color:#ffffff; border:1px solid #000000; padding:12px 14px;">
                                        <div style="font-size:10px; letter-spacing:0.6px; color:#000000; font-weight:bold; text-transform:uppercase;">
                                            {{ ($language ?? '') === 'bn' ? 'প্রেরক' : 'From' }}
                                        </div>
                                        <div style="margin-top:6px; font-size:14px; font-weight:bold; color:#000000;">
                                            {{ $options['siteTitle'] ?? 'Angon Mart' }}
                                        </div>
                                        @if (!empty($authorDetails['website']))
                                            <div style="margin-top:3px; color:#000000;">{{ $authorDetails['website'] }}</div>
                                        @endif
                                        @if (!empty($authorDetails['contact']))
                                            <div style="margin-top:2px; color:#000000;">{{ $authorDetails['contact'] }}</div>
                                        @endif
                                        @if (!empty($authorLocation['formattedAddress']))
                                            <div style="margin-top:6px; color:#000000; line-height:1.45;">{{ $authorLocation['formattedAddress'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 22px 0 22px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:1px solid #000000;">
                                <tr>
                                    <td style="padding:8px 12px; color:#000000;">
                                        {{ $label('payment_method', 'Payment method') }}:
                                        <strong style="color:#000000;">{{ $order->payment_gateway ?: '—' }}</strong>
                                    </td>
                                    <td style="padding:8px 12px; color:#000000;" align="right">
                                        {{ ($language ?? '') === 'bn' ? 'অর্ডার' : 'Order' }}:
                                        <strong style="color:#000000;">{{ $orderStatus }}</strong>
                                        <span style="color:#000000;">|</span>
                                        {{ ($language ?? '') === 'bn' ? 'পেমেন্ট' : 'Payment' }}:
                                        <strong style="color:#000000;">{{ $paymentStatus }}</strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 22px 0 22px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td width="52%" style="background-color:#000000; color:#ffffff; font-weight:bold; padding:8px 10px;">
                                        {{ $label('products', 'Products') }}
                                    </td>
                                    <td width="16%" align="center" style="background-color:#000000; color:#ffffff; font-weight:bold; padding:8px 10px;">
                                        {{ $label('quantity', 'Qty') }}
                                    </td>
                                    <td width="32%" align="right" style="background-color:#000000; color:#ffffff; font-weight:bold; padding:8px 10px;">
                                        {{ $label('total', 'Total') }}
                                    </td>
                                </tr>
                                @forelse ($products as $product)
                                    @php
                                        $lineTotal = $product->pivot['subtotal'] ?? null;
                                        if ($lineTotal === null) {
                                            $lineTotal = (float) ($product->pivot['unit_price'] ?? 0) * (float) ($product->pivot['order_quantity'] ?? 0);
                                        }
                                        $rowBg = $loop->iteration % 2 === 0 ? '#ffffff' : '#ffffff';
                                    @endphp
                                    <tr>
                                        <td style="background-color:{{ $rowBg }}; border-bottom:1px solid #000000; padding:8px 10px; color:#000000;">
                                            {{ $product['name'] }}
                                            @if (in_array($product->id, $cancelled_products))
                                                <span style="color:#000000;">({{ $label('cancelled', 'Cancelled') }})</span>
                                            @endif
                                        </td>
                                        <td align="center" style="background-color:{{ $rowBg }}; border-bottom:1px solid #000000; padding:8px 10px;">
                                            {{ $product->pivot['order_quantity'] }}
                                        </td>
                                        <td align="right" style="background-color:{{ $rowBg }}; border-bottom:1px solid #000000; padding:8px 10px;">
                                            {{ $money($lineTotal) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="padding:12px 10px; color:#000000; border-bottom:1px solid #000000;">
                                            —
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 22px 18px 22px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="52%"></td>
                                    <td width="48%" valign="top" style="background-color:#ffffff; border:1px solid #000000; padding:8px 12px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            @if (isset($order->amount))
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('subtotal', 'Subtotal') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000;">{{ $money($order->amount) }}</td>
                                                </tr>
                                            @endif
                                            @if (isset($order->discount))
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('discount', 'Discount') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000;">{{ $money($order->discount) }}</td>
                                                </tr>
                                            @endif
                                            @if (isset($order->sales_tax))
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('tax', 'Tax') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000;">{{ $money(($order->sales_tax ?? 0) + ($order->cancelled_tax ?? 0)) }}</td>
                                                </tr>
                                            @endif
                                            @if (isset($order->delivery_fee))
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('delivery_fee', 'Delivery fee') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000;">{{ $money($order->delivery_fee) }}</td>
                                                </tr>
                                            @endif
                                            @if (isset($order->cancelled_amount) && $order->cancelled_amount > 0 && $order->cancelled_tax > 0)
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('cancelled_tax', 'Tax reduced') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000;">- {{ $money($order->cancelled_tax) }}</td>
                                                </tr>
                                            @endif
                                            @if (isset($order->cancelled_amount) && $order->cancelled_amount > 0 && $order->cancelled_delivery_fee > 0)
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('cancelled_delivery_fee', 'Delivery fee reduced') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000;">- {{ $money($order->cancelled_delivery_fee) }}</td>
                                                </tr>
                                            @endif
                                            @if (isset($order->cancelled_amount) && $order->cancelled_amount > 0)
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('cancelled_subtotal', 'Cancelled subtotal') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000;">- {{ $money($order->cancelled_amount - $order->cancelled_tax - $order->cancelled_delivery_fee) }}</td>
                                                </tr>
                                            @endif
                                            @if (isset($order->total))
                                                <tr>
                                                    <td style="padding:8px 6px; background-color:#000000; color:#ffffff; font-weight:bold;">{{ $label('total', 'Total') }}</td>
                                                    <td align="right" style="padding:8px 6px; background-color:#000000; color:#ffffff; font-weight:bold;">{{ $money($order->total) }}</td>
                                                </tr>
                                            @endif
                                            @if (isset($order?->wallet_point?->amount) && !$order->shop_id)
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('paid_from_wallet', 'Paid from wallet') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000;">{{ $money($order->wallet_point->amount) }}</td>
                                                </tr>
                                            @endif
                                            @if (!$order->shop_id)
                                                <tr>
                                                    <td style="padding:4px 0; color:#000000;">{{ $label('amount_due', 'Amount due') }}</td>
                                                    <td align="right" style="padding:4px 0; color:#000000; font-weight:bold;">{{ $money($amountDue) }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#000000; color:#ffffff; padding:12px 22px; font-size:11px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#ffffff;">
                                        {{ ($language ?? '') === 'bn' ? 'কেনাকাটার জন্য ধন্যবাদ।' : 'Thank you for shopping with us.' }}
                                    </td>
                                    <td align="right" style="color:#ffffff;">
                                        {{ $authorDetails['website'] ?? ($options['siteTitle'] ?? 'Angon Mart') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
