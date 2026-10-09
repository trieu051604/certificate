<?php

namespace App\Http\Controllers;
use App\Models\PersonCertificationTest;
use App\Models\CertificationTest;
use App\Models\QuestionCertificationTest;
use Illuminate\Http\Request;
use App\Services\CertificatePdf;

class CertificationClientController extends Controller
{
    public function show($slug)
    {
        $course = CertificationTest::with(['questions.answers', 'registrationForm'])
            ->where('slug', $slug)->where('status', 'ACTIVE')->first();

        if (!$course || $course->questions->isEmpty()) {
            return view('certification-unavailable', ['message' => 'Khóa học chưa mở!']);
        }
        if ($course->date_expire && \Carbon\Carbon::parse($course->date_expire)->endOfDay() < now()) {
            return view('certification-unavailable', ['message' => 'HẾT HẠN LÀM BÀI!']);
        }

        $course->setAttribute('registration_fields', $course->registrationFields());
        $course->questions->each(fn ($question) => $question->makeHidden('answer_correct_id'));
        $provinces = \DB::table('province_certification')->select(['province', 'id'])->get();
        $schools = \DB::table('school_certification')->select(['school', 'id', 'province_id'])->get();

        if ($course->type === 'CERTIFICATION') {
            return view('submit-certification', compact('provinces', 'schools', 'course'));
        } else {
            return view('submit-game', compact('provinces', 'schools', 'course'));
        }

    }



     public function index()
    {
        return redirect(rtrim(config('certificates.frontend_url'), '/').'/chung-chi/tra-cuu');
    }

     public function search(Request $request)
    {
        return redirect(rtrim(config('certificates.frontend_url'), '/').'/chung-chi/tra-cuu');
    }

    /**
     * Tải chứng chỉ PDF
     */
    public function downloadCertification($studentId)
    {
        abort_unless(request()->hasValidSignature() || (auth()->check() && auth()->user()->type === 'ADMIN'), 403);
        $student = PersonCertificationTest::findOrFail($studentId);

        // Kiểm tra học viên có đạt hay không
        if (!$student->certification || !$student->isPass()) {
            return back()->with('error', 'Học viên chưa đạt, không thể tải chứng chỉ');
        }

        // Lấy dữ liệu cần thiết
        $infoSendMail = [
            'name' => $student->fullname,
            'email' => $student->email,
            'phone' => $student->phone,
            'registration_data' => $student->registration_data ?? [],
            'certification' => $student->certification,
            'title' => $student->certification->course,
            'course' => $student->certification->course,
            'completed_date' => $student->created_at->format('d/m/Y'),
            'school' =>  ucwords($student->name_school ?: ($student->school?->school ?? '')),
            'province' =>  $student->province?->province ?? '',

        ];

        $pdf = app(CertificatePdf::class)->make($infoSendMail);

        // Tải PDF trực tiếp mà không lưu trữ
        $filename = "chungchi_{$student->fullname}_{$student->created_at->format('dmY')}.pdf";
        
        return $pdf->download($filename)->header('Cache-Control', 'private, no-store');
    }


    
}
