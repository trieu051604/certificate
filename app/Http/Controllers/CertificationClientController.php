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

        return view('course.index');
    }

     public function search(Request $request)
    {

        if ($request->filled('website')) {
            abort(403, 'Spam detected');
        }


        $request->validate([
            'search' => 'required|string|min:3'
        ]);

        $search = $request->input('search');
        
        // Tìm kiếm theo SĐT hoặc Email
        $students = PersonCertificationTest::where('phone', $search)
                         ->orWhere('email', $search)
                         ->orderBy('id','desc')
                         ->paginate(10);
        $student = PersonCertificationTest::where('phone', $search)
                         ->orWhere('email', $search)
                         ->first();

        if (!$student) {
            return back()->with('error', 'Không tìm thấy học viên');
        }

        // Lấy danh sách khóa học
        // Giả sử bạn có bảng course_student hoặc tương tự
        // $courses = $student->certification()->paginate(10);
        
        // Hoặc nếu bạn lưu khóa học trực tiếp trong bảng students:
        // $courses = Course::where('certification_test_id', $student->certification_test_id)->get();

        return view('course.index', compact('student', 'students' ));
    }


    /**
     * Tải chứng chỉ PDF
     */
    public function downloadCertification($studentId)
    {
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
        
        return $pdf->download($filename);
    }


    
}
