@extends('adminlte::page')
@section('title', $form->exists ? 'Chỉnh sửa form đăng ký' : 'Tạo form đăng ký')
@section('content_header')
<h1>{{ $form->exists ? 'Chỉnh sửa form đăng ký' : 'Tạo form đăng ký' }}</h1>
@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('form_warning'))<div class="alert alert-warning" role="alert">{{ session('form_warning') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if($form->exists && $form->courses()->exists())
    <div class="alert alert-info">Thay đổi form sẽ áp dụng cho các lần đăng ký tiếp theo của tất cả khóa học đang chọn form này. Thông tin thí sinh đã nộp được giữ nguyên.</div>
@endif
<form method="POST" action="{{ $form->exists ? route('registration-forms.update', $form) : route('registration-forms.store') }}">
    @csrf @if($form->exists) @method('PUT') @endif
    <div class="form-group"><label for="form-name">Tên form</label><input class="form-control" id="form-name" name="name" value="{{ old('name', $form->name) }}" required maxlength="255" placeholder="Ví dụ: Đăng ký dành cho giáo viên"></div>
    <div id="app">
        <registration-form-builder schema="{{ old('fields_json', json_encode($form->fields, JSON_UNESCAPED_UNICODE)) }}" provinces="{{ $provinces->toJson() }}"></registration-form-builder>
    </div>
    <a class="btn btn-secondary" href="{{ route('registration-forms.index') }}">Quay lại</a>
    <button class="btn btn-success" type="submit">Lưu form</button>
</form>
@stop
@section('js')
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@stop
