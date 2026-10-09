<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CertificateTemplateController extends Controller
{
    public function index()
    {
        $templates = CertificateTemplate::withCount('courses')->orderBy('created_at')->orderBy('id')->get()->map(function (CertificateTemplate $template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'background' => $template->background,
                'url' => asset($template->background),
                'width' => $template->width,
                'height' => $template->height,
                'layout' => $template->layout,
                'is_default' => $template->background === config('certificates.default_background'),
                'is_in_use' => $template->background === config('certificates.default_background') || $template->courses_count > 0,
                'courses_count' => $template->courses_count,
                'is_uploaded' => str_starts_with($template->background, 'certificate-templates/'),
            ];
        });

        return view('admin.certificate-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.certificate-templates.create');
    }

    public function store(Request $request)
    {
        if (is_string($request->input('name'))) {
            $request->merge(['name' => trim($request->input('name'))]);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'layout' => ['sometimes', 'required', Rule::in(array_keys(config('certificates.layouts')))],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'name.required' => 'Vui lòng nhập tên chứng chỉ.',
            'name.max' => 'Tên chứng chỉ không được vượt quá 255 ký tự.',
            'image.required' => 'Vui lòng chọn ảnh chứng chỉ.',
            'image.image' => 'File tải lên phải là ảnh.',
            'image.mimes' => 'Chỉ hỗ trợ ảnh JPG, PNG hoặc WebP.',
            'image.extensions' => 'Chỉ hỗ trợ file có đuôi JPG, PNG hoặc WebP.',
            'image.max' => 'Ảnh chứng chỉ không được vượt quá 5 MB.',
            'image.uploaded' => 'Không thể tải ảnh lên. Vui lòng chọn ảnh tối đa 5 MB.',
        ]);

        $dimensions = getimagesize($validated['image']->getRealPath());
        $path = $validated['image']->store('', 'certificate_templates');

        try {
            CertificateTemplate::create([
                'name' => $validated['name'],
                'background' => 'certificate-templates/'.$path,
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'layout' => $validated['layout'] ?? 'school',
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('certificate_templates')->delete($path);
            throw $exception;
        }

        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Đã thêm chứng chỉ thành công.');
    }

    public function updateLayout(Request $request, CertificateTemplate $certificateTemplate)
    {
        $validated = $request->validate([
            'layout' => ['required', Rule::in(array_keys(config('certificates.layouts')))],
        ]);
        $certificateTemplate->update($validated);

        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Đã cập nhật bố cục điền thông tin của chứng chỉ.');
    }

    public function destroy(CertificateTemplate $certificateTemplate)
    {
        if ($certificateTemplate->background === config('certificates.default_background') || $certificateTemplate->courses()->exists()) {
            return redirect()->route('admin.certificate-templates.index')
                ->with('error', 'Không thể xóa mẫu chứng chỉ đang dùng cho bài thi.');
        }

        DB::transaction(function () use ($certificateTemplate) {
            $certificateTemplate->delete();

            // Only remove uploads from the dedicated disk; bundled source assets stay intact.
            if (preg_match('/\Acertificate-templates\/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)\z/', $certificateTemplate->background)) {
                if (!Storage::disk('certificate_templates')->delete(basename($certificateTemplate->background))) {
                    throw new \RuntimeException('Không thể xóa ảnh chứng chỉ.');
                }
            }
        });

        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Đã xóa chứng chỉ thành công.');
    }
}
