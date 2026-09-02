<?php

namespace Marvel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Marvel\Enums\ProductStatus;
use Marvel\Enums\ProductType;

class ProductUpdateRequest extends FormRequest
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
        $productStatus = [
            ProductStatus::UNDER_REVIEW,
            ProductStatus::APPROVED,
            ProductStatus::REJECTED,
            ProductStatus::PUBLISH,
            ProductStatus::UNPUBLISH,
            ProductStatus::DRAFT,
        ];

        $productType = [
            ProductType::SIMPLE,
            ProductType::VARIABLE
        ];

        $rules = [
            'name'                         => ['string', 'max:255'],
            'price'                        => ['nullable', 'numeric'],
            'sale_price'                   => ['nullable', 'lte:price'],
            'type_id'                      => ['exists:Marvel\Database\Models\Type,id'],
            'manufacturer_id'              => ['nullable', 'exists:Marvel\Database\Models\Manufacturer,id'],
            'author_id'                    => ['nullable', 'exists:Marvel\Database\Models\Author,id'],
            'categories'                   => ['exists:Marvel\Database\Models\Category,id'],
            'tags'                         => ['exists:Marvel\Database\Models\Tag,id'],
            'dropoff_locations'            => ['nullable', 'array'],
            'pickup_locations'             => ['nullable', 'array'],
            'language'                     => ['nullable', 'string'],
            'digital_file'                 => ['nullable', 'array'],
            'product_type'                 => ['required', Rule::in($productType)],
            'unit'                         => ['string'],
            'description'                  => ['nullable', 'string', 'max:10000'],
            'quantity'                     => ['nullable', 'integer'],
            'image'                        => ['nullable', 'array'],
            'gallery'                      => ['nullable', 'array'],
            'video'                        => ['nullable', 'array'],
            'video_file'                   => ['nullable', 'array'],
            'video_file.id'                => ['nullable'],
            'video_file.original'          => ['nullable', 'string'],
            'video_file.thumbnail'         => ['nullable', 'string'],
            'status'                       => ['string', Rule::in($productStatus)],
            'height'                       => ['nullable', 'string'],
            'length'                       => ['nullable', 'string'],
            'width'                        => ['nullable', 'string'],
            'external_product_url'         => ['nullable', 'string'],
            'external_product_button_text' => ['nullable', 'string'],
            'in_stock'                     => ['boolean'],
            'is_preorder'                  => ['boolean'],
            'preorder_available_at'        => ['nullable', 'date'],
            'is_taxable'                   => ['boolean'],
            'is_digital'                   => ['boolean'],
            'is_external'                  => ['boolean'],
            'is_rental'                    => ['boolean'],
            'variation_options'            => ['array'],
            'variation_options.upsert'     => ['array'],
        ];

        // Top-level SKU only applies to simple products; variable products clear it in the repository.
        if ($this->input('product_type') === ProductType::SIMPLE && filled($this->input('sku'))) {
            $rules['sku'] = [
                'nullable',
                'string',
                Rule::unique('variation_options', 'sku'),
            ];
        } else {
            $rules['sku'] = ['nullable', 'string'];
        }

        // Ignore each variation's own id so editing keeps the same SKU.
        $upserts = $this->input('variation_options.upsert', []);
        if (is_array($upserts)) {
            foreach ($upserts as $index => $option) {
                $ignoreId = is_array($option) ? ($option['id'] ?? null) : null;
                $rules["variation_options.upsert.{$index}.sku"] = [
                    'nullable',
                    'string',
                    Rule::unique('variation_options', 'sku')->ignore($ignoreId),
                ];
            }
        }

        return $rules;
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
