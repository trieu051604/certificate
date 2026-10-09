@extends('adminlte::page')

@section('title', 'Thêm chứng chỉ')

@section('content_header')
    <h1>Thêm chứng chỉ</h1>
@stop

@section('content')
    <form action="{{ route('admin.certificate-templates.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="certificate-name">Tên chứng chỉ <span class="text-danger">*</span></label>
                            <input id="certificate-name" type="text" name="name" value="{{ old('name') }}" maxlength="255" required
                                   class="form-control @error('name') is-invalid @enderror" placeholder="Ví dụ: Chứng chỉ hoàn thành khóa học AI">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="certificate-layout">Bố cục điền thông tin</label>
                            <select id="certificate-layout" name="layout" class="form-control">
                                @foreach(config('certificates.layouts') as $value => $label)
                                    <option value="{{ $value }}" @selected(old('layout', 'school') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('layout')<div class="text-danger">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">Chọn bố cục khớp với các nhãn có sẵn trên ảnh nền.</small>
                        </div>
                        <div class="form-group mb-0">
                            <label for="certificate-image">Ảnh chứng chỉ <span class="text-danger">*</span></label>
                            <input id="certificate-image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required
                                   class="form-control-file @error('image') is-invalid @enderror" aria-describedby="image-help">
                            @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <small id="image-help" class="form-text text-muted mt-3">Hỗ trợ JPG, PNG, WebP; tối đa 5 MB. Nên dùng ảnh ngang 2048 × 1448 px để phù hợp bố cục chứng chỉ.</small>
                            <small class="form-text text-muted">Nếu lưu chưa thành công, vui lòng chọn lại ảnh trước khi gửi.</small>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-outline-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1" aria-hidden="true"></i> Lưu chứng chỉ</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Xem trước ảnh chứng chỉ</h2></div>
                    <div class="card-body text-center bg-light">
                        <p id="preview-empty" class="text-muted py-5 mb-0">Chọn ảnh để xem trước.</p>
                        <img id="certificate-preview" alt="Ảnh chứng chỉ xem trước" hidden class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
        const imageInput = document.getElementById('certificate-image');
        const preview = document.getElementById('certificate-preview');
        const emptyPreview = document.getElementById('preview-empty');
        let previewUrl = null;

        imageInput.addEventListener('change', function () {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            preview.hidden = true;
            preview.removeAttribute('src');
            emptyPreview.hidden = false;
            imageInput.setCustomValidity('');

            const file = imageInput.files[0];
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                imageInput.setCustomValidity('Vui lòng chọn ảnh JPG, PNG hoặc WebP.');
                imageInput.reportValidity();
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                imageInput.setCustomValidity('Ảnh chứng chỉ không được vượt quá 5 MB.');
                imageInput.reportValidity();
                return;
            }

            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.hidden = false;
            emptyPreview.hidden = true;
        });
    </script>
@stop
