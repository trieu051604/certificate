@extends('adminlte::page')

@section('title', 'Danh sách chứng chỉ')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <h1 class="mb-2">Danh sách chứng chỉ</h1>
        <div class="d-flex align-items-center mb-2">
            <span class="badge badge-light p-2 mr-3">{{ $templates->count() }} mẫu chứng chỉ</span>
            <a href="{{ route('admin.certificate-templates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1" aria-hidden="true"></i> Thêm chứng chỉ
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @error('layout')<div class="alert alert-danger">{{ $message }}</div>@enderror
    <p class="text-muted mb-4">Xem các mẫu nền chứng chỉ trong hệ thống. Tên học viên và thông tin khóa học sẽ được điền khi cấp chứng chỉ.</p>

    <div class="row">
        @forelse($templates as $template)
            <div class="col-12 col-md-6 col-xl-4 mb-4">
                <div class="card certificate-card h-100 mb-0">
                    <a href="{{ $template['url'] }}" target="_blank" rel="noopener" class="certificate-image" aria-label="Xem {{ $template['name'] }}">
                        <img src="{{ $template['url'] }}" alt="{{ $template['name'] }}" loading="lazy">
                    </a>
                    <div class="card-body">
                        <h2 class="h5 mb-3">{{ $template['name'] }}</h2>
                        @if($template['is_in_use'])
                            <span class="badge badge-success mb-2">Đang dùng cho bài thi</span>
                        @else
                            <span class="badge badge-secondary mb-2">{{ !empty($template['is_uploaded']) ? 'Mẫu đã thêm' : 'Mẫu có sẵn' }}</span>
                        @endif
                        @if($template['courses_count'] > 0)
                            <p class="text-muted small mb-2">Được chọn cho {{ $template['courses_count'] }} khóa học</p>
                        @endif
                        @if($template['width'] && $template['height'])
                            <p class="text-muted small mb-0">{{ $template['width'] }} × {{ $template['height'] }} px</p>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <form action="{{ route('admin.certificate-templates.layout', $template['id']) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PATCH')
                            <label for="layout-{{ $template['id'] }}">Bố cục điền thông tin</label>
                            <select name="layout" id="layout-{{ $template['id'] }}" class="form-control mb-2">
                                @foreach(config('certificates.layouts') as $value => $label)
                                    <option value="{{ $value }}" @selected($template['layout'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-secondary btn-block">Lưu bố cục</button>
                        </form>
                        <a href="{{ $template['url'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-eye mr-1" aria-hidden="true"></i> Xem mẫu chứng chỉ
                        </a>
                        @if($template['is_in_use'])
                            <button type="button" class="btn btn-outline-secondary btn-block mt-2" disabled title="Mẫu đang dùng cho bài thi">
                                <i class="fas fa-lock mr-1" aria-hidden="true"></i> Đang sử dụng
                            </button>
                        @else
                            <form action="{{ route('admin.certificate-templates.destroy', $template['id']) }}" method="POST" class="mt-2 certificate-delete-form" data-template-name="{{ $template['name'] }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-block" aria-label="Xóa {{ $template['name'] }}">
                                    <i class="fas fa-trash-alt mr-1" aria-hidden="true"></i> Xóa chứng chỉ
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center text-muted py-5">Chưa có mẫu chứng chỉ trong hệ thống.</div></div>
            </div>
        @endforelse
    </div>
@stop

@section('js')
    <script>
        document.querySelectorAll('.certificate-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm('Bạn có chắc muốn xóa chứng chỉ "' + form.dataset.templateName + '"?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
@stop

@section('css')
    <style>
        .certificate-card { overflow: hidden; }
        .certificate-image { display: block; padding: 12px; background: #f4f6f9; }
        .certificate-image img { display: block; width: 100%; aspect-ratio: 2048 / 1448; object-fit: contain; }
        .certificate-card .card-body { flex: 1; }
        .certificate-card .badge { white-space: normal; }
    </style>
@stop
