<?php

namespace Marvel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutVerifyRequest extends FormRequest
{
    protected $rules = [];

    /**
     * General validation rules
     *
     * @return array
     */
    protected function getRules()
    {
        return [
            'amount'           => 'required|numeric',
            'customer_id'      => 'nullable|exists:Marvel\Database\Models\User,id',
            'products'         => 'required|array',
            'billing_address'                 => 'nullable|array',
            'shipping_address'                => 'required|array',
            'shipping_address.country'        => 'required|string|in:Bangladesh',
            'shipping_address.division_id'    => 'required|integer|exists:bd_divisions,id',
            'shipping_address.district_id'    => 'required|integer|exists:bd_districts,id',
            'shipping_address.thana_id'       => 'required|integer|exists:bd_thanas,id',
            'delivery_schedule_id'            => 'nullable|integer|exists:delivery_schedules,id',
        ];
    }

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
        return $this->getRules();
    }

    /**
     * Get the error messages that apply to the request parameters.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'products.required' => 'Product field is required',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
