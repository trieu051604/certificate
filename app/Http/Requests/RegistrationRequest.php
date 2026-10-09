<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'BOD' => 'regex:/^\d{8}$/',
            'GraduateYear' => 'integer|min:1900|max:'.date('Y'),
            'IdNumber' => 'regex:/^\d{12}$/',
            'IdIssuedDate' => 'regex:/^\d{8}$/',
            'Gender' => 'in:m,f'
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
            'Gender.in' => 'Gender nhập không đúng giá trị.'
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
