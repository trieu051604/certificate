<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PersonCertificationExport;
use App\Http\Controllers\Controller;
use App\Imports\CertificationSchoolImport;
use App\Imports\QuestionImport;
use App\Models\CertificationTest;
use App\Models\CertificateTemplate;
use App\Models\RegistrationForm;
use App\Models\ProvinceCertificationTest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CertificationTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $certifications = CertificationTest::with('registrationForm')->orderBy('created_at', 'desc')->paginate(50);
        return view('admin.certifications.index', compact('certifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $copy = $request->validate(['copy_from' => ['nullable', 'integer', Rule::exists('certification_test', 'id')->where('type', 'CERTIFICATION')]]);
        $certification = isset($copy['copy_from']) ? CertificationTest::findOrFail($copy['copy_from']) : null;
        $certificateTemplates = CertificateTemplate::orderBy('name')->get();
        $registrationForms = RegistrationForm::orderBy('name')->get();
        $provinces = ProvinceCertificationTest::select('id', 'province')->get();
        return view('admin.certifications.create', compact('certificateTemplates', 'registrationForms', 'provinces', 'certification'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateCourse($request);
        $template = CertificateTemplate::findOrFail($validated['certificate_template_id']);
        $certification = CertificationTest::create([
            'course' => $request->course,
            'slug' => CertificationTest::availableSlug($validated['course']),
            'passing_score' => $request->passing_score,
            'status' => $request->status,
            'title' => $template->name,
            'certificate_template_id' => $template->id,
            'registration_form_id' => $validated['registration_form_id'] ?? null,
            'content' => $request->content,
            'subject' => $request->subject,
            'date_expire' => $request->date_expire,
        ]);

        if ($request->filled('copy_from')) {
            return redirect()->route('admin.certification.import', $certification->id)
                ->with(['level' => 'success', 'message' => 'Đã tạo bản sao khóa học. Chọn file để import bộ câu hỏi mới.']);
        }
        return redirect()->route('certification.index')->with(['level' => 'success', 'message' => 'Thêm thành công!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $certification = CertificationTest::find($id);
        $query = $certification->persons();
        if (request()->phone) {
            $query = $query->where('phone', 'LIKE', '%'.request()->phone.'%');
        }
        if (request()->you_is) {
            $query = $query->where('you_is', 'LIKE', '%'.request()->you_is.'%');
        }


        if (request()->email) {
            $query = $query->where('email', 'LIKE', '%'.request()->email.'%');
        }
        if (request()->startDate && request()->endDate) {
            $query = $query->whereDate('created_at', '>=', request()->startDate)
                            ->whereDate('created_at', '<=', request()->endDate);
        }
        $lists = (clone $query)->orderBy('created_at', 'desc')->paginate(100);
        $count = $query->count();
        return view('admin.certifications.list-person-certification', compact('lists', 'count', 'certification'));
    }

    public function showGame(string $id)
    {
        $certification = CertificationTest::find($id);
        $query = $certification->person_game();
        if (request()->phone) {
            $query = $query->where('phone', 'LIKE', '%'.request()->phone.'%');
        }
        if (request()->email) {
            $query = $query->where('email', 'LIKE', '%'.request()->email.'%');
        }
        if (request()->startDate && request()->endDate) {
            $query = $query->whereDate('created_at', '>=', request()->startDate)
                            ->whereDate('created_at', '<=', request()->endDate);
        }
        $lists = (clone $query)->orderBy('created_at', 'desc')->paginate(100);
        $count = $query->count();
        return view('admin.certifications.list-person-game', compact('lists', 'count', 'certification'));
    }

    public function exportDataPerson($id)
    {
        $certification = CertificationTest::find($id);
        $query = $certification->persons();
        if (request()->phone) {
            $query = $query->where('phone', 'LIKE', '%'.request()->phone.'%');
        }
        if (request()->email) {
            $query = $query->where('email', 'LIKE', '%'.request()->email.'%');
        }
        if (request()->startDate && request()->endDate) {
            $query = $query->whereDate('created_at', '>=', request()->startDate)
                            ->whereDate('created_at', '<=', request()->endDate);
        }
        $data = $query->get();
        return Excel::download(new PersonCertificationExport($data), 'danh-sach-thi-chung-chi.xlsx');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $certification = CertificationTest::findOrFail($id);
        $certificateTemplates = CertificateTemplate::orderBy('name')->get();
        $registrationForms = RegistrationForm::orderBy('name')->get();
        $provinces = ProvinceCertificationTest::select('id', 'province')->get();
        return view('admin.certifications.update', compact('certification', 'certificateTemplates', 'registrationForms', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $this->validateCourse($request);
        $template = CertificateTemplate::findOrFail($validated['certificate_template_id']);
        $certification = CertificationTest::findOrFail($id);
        $certification->update([
            'course' => $request->course,
            'slug' => $certification->course === $validated['course']
                ? $certification->slug
                : CertificationTest::availableSlug($validated['course'], $certification->id),
            'passing_score' => $request->passing_score,
            'status' => $request->status,
            'title' => $template->name,
            'certificate_template_id' => $template->id,
            'registration_form_id' => $validated['registration_form_id'] ?? null,
            'content' => $request->content,
            'subject' => $request->subject,
            'date_expire' => $request->date_expire,
        ]);
        return redirect()->back()->with(['level' => 'success', 'message' => 'Cập nhật thành công!']);
    }

    private function validateCourse(Request $request): array
    {
        return $request->validate([
            'course' => ['required', 'string', 'max:255'],
            'copy_from' => ['nullable', 'integer', Rule::exists('certification_test', 'id')->where('type', 'CERTIFICATION')],
            'passing_score' => ['required', 'integer', 'between:1,100'],
            'status' => ['required', Rule::in(['ACTIVE', 'UNACTIVE'])],
            'date_expire' => ['nullable', 'date'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'certificate_template_id' => ['required', 'integer', Rule::exists('certificate_templates', 'id')->whereNull('deleted_at')],
            'registration_form_id' => ['nullable', 'integer', Rule::exists('registration_forms', 'id')->whereNull('deleted_at')],
        ], [
            'certificate_template_id.required' => 'Vui lòng chọn chứng chỉ.',
            'certificate_template_id.integer' => 'Chứng chỉ đã chọn không hợp lệ.',
            'certificate_template_id.exists' => 'Chứng chỉ không còn trong hệ thống. Vui lòng chọn lại.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function import($id)
    {
        $certification = CertificationTest::with('questions.answers')->withCount(['persons', 'person_game'])->findOrFail($id);
        return view('admin.certifications.import-question', compact('certification'));
    }

    public function importStore(Request $request)
    {
        $validated = $request->validate(['certification_id' => ['required', 'integer', 'exists:certification_test,id']]);
        $certification = CertificationTest::findOrFail($validated['certification_id']);

        if ($certification->persons()->exists() || $certification->person_game()->exists()) {
            return redirect()->route('admin.certification.import', $certification->id)
                ->with(['level' => 'warning', 'message' => 'Khóa học đã có bài nộp nên không thể thay bộ câu hỏi. Vui lòng tạo bản sao khóa học để import bộ câu hỏi mới.']);
        }
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']], [
            'file.required' => 'Vui lòng chọn file câu hỏi.',
            'file.mimes' => 'Vui lòng chọn file Excel XLSX, XLS hoặc CSV.',
            'file.max' => 'File câu hỏi không được vượt quá 5 MB.',
        ]);

        try {
            Excel::import(new QuestionImport($certification), $request->file('file'));
            return redirect()->route('admin.certification.import', $certification->id)
                ->with(['level' => 'success', 'message' => 'Đã import '.$certification->questions()->count().' câu hỏi vào khóa #'.$certification->id.'.']);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()->route('admin.certification.import', $certification->id)->withErrors($exception->errors());
        } catch (\Throwable $th) {
            \Log::warning('Question import failed', ['course_id' => $certification->id, 'error' => $th->getMessage()]);
            return redirect()->route('admin.certification.import', $certification->id)
                ->with(['level' => 'danger', 'message' => 'Không thể đọc file câu hỏi. Kiểm tra định dạng file hoặc lưu lại thành XLSX rồi thử lại.']);
        }
    }

    public function schoolImport()
    {
        $provinces = ProvinceCertificationTest::all();
        return view('admin.certifications.import-school', compact('provinces'));
    }

    public function schoolImportStore(Request $request)
    {
        $file = $request->file('file');
        
        if (!$request->province_id) {
            return redirect()->back()->with(['level' => 'error', 'message' => 'Vui lòng chọn tỉnh!']);
        }

        try {
            Excel::import(new CertificationSchoolImport($request->province_id), $file);
            return redirect()->back()->with(['level' => 'success', 'message' => 'Thêm thành công!']);
        } catch (\Throwable $th) {
            return redirect()->back()->with(['level' => 'error', 'message' => 'Thêm thất bại!']);
        }
    }
}
