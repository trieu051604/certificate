@php
    $defaultTemplate = $certificateTemplates->firstWhere('background', config('certificates.default_background'));
    $selectedTemplateId = old('certificate_template_id', isset($certification) ? ($certification->certificate_template_id ?? $defaultTemplate?->id) : $defaultTemplate?->id);
@endphp
<div class="form-group">
    <label for="certificate-template-id">Tên chứng chỉ</label>
    <select id="certificate-template-id" name="certificate_template_id" required class="form-control @error('certificate_template_id') is-invalid @enderror">
        <option value="">-- Chọn chứng chỉ --</option>
        @foreach($certificateTemplates as $certificateTemplate)
            <option value="{{ $certificateTemplate->id }}" data-image="{{ asset($certificateTemplate->background) }}" @selected((string) $selectedTemplateId === (string) $certificateTemplate->id)>
                {{ $certificateTemplate->name }}
            </option>
        @endforeach
    </select>
    @error('certificate_template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="form-text text-muted">Chọn mẫu dùng để cấp chứng chỉ cho khóa học. <a href="{{ route('admin.certificate-templates.index') }}" target="_blank" rel="noopener">Xem danh sách chứng chỉ</a></small>
    @if($certificateTemplates->isEmpty())
        <p class="text-warning mt-2">Chưa có chứng chỉ. Vui lòng thêm mẫu trong danh sách chứng chỉ trước khi lưu khóa học.</p>
    @endif
    <a id="certificate-selected-link" href="#" target="_blank" rel="noopener" hidden>
        <img id="certificate-selected-preview" alt="Mẫu chứng chỉ đã chọn" class="mt-3" style="max-width:100%;width:360px;height:auto;border:1px solid #dee2e6;border-radius:4px;">
    </a>
</div>

@push('js')
<script>
    const certificateSelect = document.getElementById('certificate-template-id');
    const certificatePreviewLink = document.getElementById('certificate-selected-link');
    const certificatePreviewImage = document.getElementById('certificate-selected-preview');
    function showSelectedCertificate() {
        const selectedOption = certificateSelect.options[certificateSelect.selectedIndex];
        const imageUrl = selectedOption ? selectedOption.dataset.image : null;
        certificatePreviewLink.hidden = !imageUrl;
        if (imageUrl) {
            certificatePreviewLink.href = imageUrl;
            certificatePreviewImage.src = imageUrl;
            certificatePreviewImage.alt = selectedOption.textContent.trim();
        } else {
            certificatePreviewImage.removeAttribute('src');
        }
    }
    certificateSelect.addEventListener('change', showSelectedCertificate);
    showSelectedCertificate();
</script>
@endpush
