<div class="form-group" id="app">
    <registration-form-picker
        forms="{{ $registrationForms->map(fn($form) => ['id' => $form->id, 'name' => $form->name, 'fields' => $form->fields])->toJson(JSON_UNESCAPED_UNICODE) }}"
        initial="{{ old('registration_form_id', $certification->registration_form_id ?? '') }}"
        defaults="{{ json_encode(\App\Services\RegistrationFields::defaults(isset($certification) && $certification->type !== 'CERTIFICATION'), JSON_UNESCAPED_UNICODE) }}"
        provinces="{{ $provinces->toJson() }}"
    ></registration-form-picker>
    <a href="{{ route('registration-forms.index') }}" target="_blank" rel="noopener">Quản lý / Tạo form đăng ký</a>
    @error('registration_form_id')<p class="text-danger">{{ $message }}</p>@enderror
</div>
@push('js')
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@endpush
