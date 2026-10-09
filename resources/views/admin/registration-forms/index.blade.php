@extends('adminlte::page')
@section('title', 'Form đăng ký')
@section('content_header')
<h1>Form đăng ký</h1>
@stop
@section('content')
@foreach(['success', 'error'] as $message)
    @if(session($message))<div class="alert alert-{{ $message === 'error' ? 'danger' : 'success' }}">{{ session($message) }}</div>@endif
@endforeach
<p>Tạo form thông tin thí sinh, xem trước và chọn form cho từng khóa học trong mục Thi Chứng chỉ.</p>
<a class="btn btn-primary mb-3" href="{{ route('registration-forms.create') }}"><i class="fas fa-plus"></i> Tạo form đăng ký</a>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Tên form</th><th>Số trường</th><th>Khóa học sử dụng</th><th>Thao tác</th></tr></thead>
    <tbody>@forelse($forms as $form)
    <tr><td>{{ $form->name }}</td><td>{{ count($form->fields) }}</td><td>{{ $form->courses_count }}</td>
        <td><a class="btn btn-outline-primary btn-sm" href="{{ route('registration-forms.edit', $form) }}">Chỉnh sửa / Xem trước</a>
            <form class="d-inline" method="POST" action="{{ route('registration-forms.destroy', $form) }}" onsubmit="return confirm('Xóa form đăng ký này?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" @disabled($form->courses_count > 0)>Xóa</button>
            </form>
        </td></tr>
    @empty<tr><td colspan="4">Chưa có form riêng. Các khóa học đang sử dụng form mặc định.</td></tr>@endforelse</tbody>
</table></div></div>
{{ $forms->links() }}
@stop
