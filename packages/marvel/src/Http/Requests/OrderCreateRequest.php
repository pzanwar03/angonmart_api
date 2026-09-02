<?php

namespace Marvel\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Marvel\Enums\PaymentGatewayType;

class OrderCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'coupon_id'               => 'nullable|exists:Marvel\Database\Models\Coupon,id',
            'customer_id'             => 'nullable|exists:Marvel\Database\Models\User,id',
            'language'                => ['nullable', 'string'],
            'amount'                  => 'required|numeric',
            'paid_total'              => 'required|numeric',
            'total'                   => 'required|numeric',
            'delivery_time'           => 'nullable|string',
            'customer_contact'        => 'string|required',
            'customer_name'           => 'nullable|string',
            'payment_gateway'         => ['required', Rule::in(PaymentGatewayType::getValues())],
            'altered_payment_gateway' => 'nullable|string',
            'products'                => 'required|array',
            'card'                    => 'array',
            'token'                   => 'nullable|string',
            'use_wallet_points'       => 'nullable|boolean',
            'shipping_address'                       => 'nullable|array',
            'shipping_address.country'               => 'required_with:shipping_address.division_id|string|in:Bangladesh',
            'shipping_address.division_id'           => 'required_with:shipping_address.country|integer|exists:bd_divisions,id',
            'shipping_address.district_id'           => 'required_with:shipping_address.country|integer|exists:bd_districts,id',
            'shipping_address.thana_id'              => 'required_with:shipping_address.country|integer|exists:bd_thanas,id',
            'shipping_address.street_address'        => 'required_with:shipping_address.country|string',
            'billing_address'                        => 'nullable|array',
            'note'                                   => 'nullable|string',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
