<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'BOD' => 'required|regex:/^\d{8}$/',
            'GraduateYear' => 'required|integer|min:1900|max:'.date('Y'),
            'IdNumber' => 'required|regex:/^\d{12}$/',
            'IdIssuedDate' => 'required|regex:/^\d{8}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'BOD.required' => 'Thiếu params BOD.',
            'BOD.regex' => 'BOD không hợp lệ. Định dạng phải là yyyyMMDD.',
            'GraduateYear.required' => 'Thiếu params GraduateYear.',
            'GraduateYear.integer' => 'GraduateYear phải là một số nguyên.',
            'GraduateYear.min' => 'GraduateYear không được nhỏ hơn 1900.',
            'GraduateYear.max' => 'GraduateYear không được lớn hơn năm hiện tại.',
            'IdNumber.required' => 'Thiếu params IdNumber.',
            'IdNumber.regex' => 'IdNumber không hợp lệ. Phải có 12 chữ số.',
            'IdIssuedDate.required' => 'Thiêu params IdIssuedDate.',
            'IdIssuedDate.regex' => 'IdIssuedDate không hợp lệ. Định dạng phải là yyyyMMDD.',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(jsonResponse("ok", "99", $validator->errors()->first()));
    }
}
