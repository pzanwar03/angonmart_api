<?php


namespace Marvel\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class AttachmentRequest extends FormRequest
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
        $maxKilobytes = 50 * 1024; // 50MB

        return [
            'attachment'   => ['required'],
            'attachment.*' => [
                'file',
                'max:' . $maxKilobytes,
                function ($attribute, $value, $fail) {
                    if (!$value || !method_exists($value, 'getMimeType')) {
                        return;
                    }
                    $mime = (string) $value->getMimeType();
                    if (str_starts_with($mime, 'video/') && !in_array($mime, [
                        'video/mp4',
                        'video/webm',
                        'video/quicktime',
                    ], true)) {
                        $fail('Unsupported video format. Allowed: mp4, webm, mov.');
                    }
                },
            ],
        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
