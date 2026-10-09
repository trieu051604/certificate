<?php

namespace App\Services;

use App\Models\CertificationTest;
use App\Models\ProvinceCertificationTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegistrationFields
{
    public const SYSTEM_TYPES = ['fullname' => 'text', 'email' => 'email', 'phone' => 'tel', 'name_school' => 'text', 'province' => 'province', 'you_is' => 'select', 'major' => 'select'];

    public static function defaults(bool $game = false): array
    {
        $fields = [
            ['key' => 'fullname', 'label' => 'Tên thí sinh', 'type' => 'text', 'required' => true, 'placeholder' => '', 'options' => []],
            ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => '', 'options' => []],
            ['key' => 'phone', 'label' => 'Số điện thoại', 'type' => 'tel', 'required' => true, 'placeholder' => '', 'options' => []],
            ['key' => 'province', 'label' => 'Tỉnh/Thành phố', 'type' => 'province', 'required' => true, 'placeholder' => '', 'options' => []],
            ['key' => 'you_is', 'label' => 'Bạn là', 'type' => 'select', 'required' => false, 'placeholder' => '', 'options' => ['Giáo viên', 'Phụ huynh', 'Học sinh lớp 12', 'Học sinh lớp 11', 'Học sinh lớp 10', 'Khác']],
            ['key' => 'name_school', 'label' => 'Trường THPT', 'type' => 'text', 'required' => !$game, 'placeholder' => '', 'options' => []],
        ];
        if ($game) {
            $fields[] = ['key' => 'major', 'label' => 'Ngành', 'type' => 'select', 'required' => true, 'placeholder' => '', 'options' => ['Chưa xác định', 'An toàn thông tin', 'Kỹ thuật phần mềm', 'Thiết kế mỹ thuật số', 'Trí tuệ nhân tạo', 'Hệ thống thông tin', 'Công nghệ ô tô số', 'Thiết kế vi mạch bán dẫn', 'Quản trị khách sạn', 'Quản trị dịch vụ Du lịch và Lữ hành', 'Kinh doanh quốc tế', 'Digital Marketing', 'Tài chính', 'Quản lý Logistics và chuỗi cung ứng toàn cầu', 'Truyền thông đa phương tiện', 'Quan hệ công chúng', 'Ngôn ngữ Anh', 'Song ngữ Nhật - Anh', 'Song ngữ Hàn - Anh', 'Song ngữ Trung - Anh']];
        }
        return $fields;
    }

    public function schema(string $json): array
    {
        $fields = json_decode($json, true);
        Validator::make(['fields' => $fields], [
            'fields' => ['required', 'array', 'min:2', 'max:30'],
            'fields.*' => ['array:key,label,type,required,placeholder,options'],
            'fields.*.key' => ['required', 'string', 'max:64', 'regex:/\A(?:fullname|email|phone|name_school|province|you_is|major|custom_[a-z0-9_]+)\z/', 'distinct'],
            'fields.*.label' => ['required', 'string', 'max:150'],
            'fields.*.type' => ['required', Rule::in(['text', 'textarea', 'email', 'tel', 'date', 'number', 'select', 'province'])],
            'fields.*.required' => ['required', 'boolean'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.options' => ['present', 'array', 'max:100'],
            'fields.*.options.*' => ['required', 'string', 'max:255'],
        ], ['fields.required' => 'Vui lòng thêm các trường cho form.'])->validate();
        if (!array_is_list($fields)) {
            throw ValidationException::withMessages(['fields_json' => 'Danh sách trường không hợp lệ.']);
        }
        $keys = array_column($fields, 'key');
        foreach (['fullname', 'email'] as $core) {
            if (!in_array($core, $keys, true)) {
                throw ValidationException::withMessages(['fields_json' => 'Form phải có họ tên và email để cấp chứng chỉ.']);
            }
        }
        foreach ($fields as &$field) {
            $field['label'] = trim($field['label']);
            $field['placeholder'] = trim($field['placeholder'] ?? '');
            $field['required'] = (bool) $field['required'];
            if (!$field['label'] || (isset(self::SYSTEM_TYPES[$field['key']]) && self::SYSTEM_TYPES[$field['key']] !== $field['type']) || ($field['type'] === 'province' && $field['key'] !== 'province')) {
                throw ValidationException::withMessages(['fields_json' => 'Tên hoặc kiểu trường không hợp lệ.']);
            }
            if (in_array($field['key'], ['fullname', 'email'], true) && !$field['required']) {
                throw ValidationException::withMessages(['fields_json' => 'Họ tên và email phải là trường bắt buộc.']);
            }
            $field['options'] = array_values(array_unique(array_map('trim', $field['options'])));
            if ($field['type'] === 'select' && (!$field['options'] || in_array('', $field['options'], true))) {
                throw ValidationException::withMessages(['fields_json' => 'Trường danh sách cần ít nhất một lựa chọn không trống.']);
            }
            if ($field['type'] !== 'select') $field['options'] = [];
        }
        return $fields;
    }

    public function submission(Request $request, CertificationTest $course): array
    {
        $fields = $course->registrationFields();
        $data = $request->input('registration', $request->only(array_column($fields, 'key')));
        if (!is_array($data)) throw ValidationException::withMessages(['registration' => 'Thông tin đăng ký không hợp lệ.']);
        $rules = []; $labels = [];
        foreach ($fields as $field) {
            $key = $field['key'];
            $rules[$key] = [$field['required'] ? 'required' : 'nullable'];
            $rules[$key][] = $field['type'] === 'province' ? 'integer' : 'string';
            if ($field['type'] !== 'province') $rules[$key][] = 'max:'.(isset(self::SYSTEM_TYPES[$key]) ? '255' : '2000');
            if ($field['type'] === 'email') $rules[$key][] = 'email';
            if ($field['type'] === 'tel') $rules[$key][] = 'regex:/\A[0-9]{10,11}\z/';
            if ($field['type'] === 'date') $rules[$key][] = 'date_format:Y-m-d';
            if ($field['type'] === 'number') $rules[$key][] = 'numeric';
            if ($field['type'] === 'select') $rules[$key][] = Rule::in($field['options']);
            if ($field['type'] === 'province') $rules[$key][] = Rule::exists('province_certification', 'id');
            if (isset($data[$key]) && is_string($data[$key])) $data[$key] = trim($data[$key]);
            $labels[$key] = $field['label'];
        }
        $values = Validator::make($data, $rules, [
            'required' => ':attribute không được để trống.', 'email' => ':attribute không đúng định dạng email.',
            'regex' => ':attribute cần 10–11 chữ số.', 'in' => ':attribute không nằm trong danh sách lựa chọn.',
            'date_format' => ':attribute không đúng định dạng ngày.', 'exists' => ':attribute không hợp lệ.',
        ], $labels)->validate();
        $snapshot = [];
        foreach ($fields as $field) {
            $value = $values[$field['key']] ?? '';
            if ($field['type'] === 'province' && $value) $value = ProvinceCertificationTest::find($value)?->province ?? '';
            $snapshot[$field['key']] = ['label' => $field['label'], 'value' => $value];
        }
        return ['values' => $values, 'snapshot' => $snapshot];
    }
}
