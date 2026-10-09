<div class="form-group">
    <label for="subject">Tiêu đề email</label>
    <input type="text" class="form-control" id="subject" name="subject" maxlength="255"
           value="{{ old('subject', $certification->subject ?? config('certificates.email_subject')) }}">
</div>
<div class="form-group">
    <label for="editor">Nội dung email</label>
    <textarea id="editor" name="content" cols="30">{{ old('content', $certification->content ?? config('certificates.email_content')) }}</textarea>
</div>
<div class="form-group">
    <small class="form-text text-muted mb-2">
        Thông tin tự điền khi gửi: <code>[name]</code> họ tên,
        <code>[unit]</code> đơn vị, <code>[course]</code> khóa học,
        <code>[cer]</code> tên mẫu chứng chỉ đã chọn, <code>[completed_date]</code> ngày hoàn thành.
        PDF đính kèm dùng mẫu chứng chỉ của khóa học. Để trống tiêu đề/nội dung để dùng mẫu email mặc định.
    </small>
    <button id="use-certificate-email" type="button" class="btn btn-outline-primary"
            data-subject="{{ config('certificates.email_subject') }}" data-content="{{ config('certificates.email_content') }}">
        Dùng mẫu email chứng chỉ
    </button>
    <small class="form-text text-muted">Bấm Lưu/Cập nhật khóa học để áp dụng nội dung email.</small>
</div>
