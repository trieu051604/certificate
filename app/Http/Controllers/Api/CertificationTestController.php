<?php

namespace App\Http\Controllers\Api;

use App\Jobs\SendCertificationMailJob;
use App\Mail\SendCertificationMail;
use App\Mail\SendCertificationMailV2;
use App\Models\CertificationTest;
use App\Models\PersonCertificationTest;
use App\Models\PersonGameCertificationTest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
class CertificationTestController extends Controller
{
    private function prepareRegistration(Request $request): array
    {
        $request->validate(['courseId' => 'required|integer|exists:certification_test,id']);
        $course = CertificationTest::with('registrationForm', 'questions.answers')->findOrFail($request->courseId);
        if ($course->status !== 'ACTIVE' || ($course->date_expire && Carbon::parse($course->date_expire)->endOfDay()->isPast())) {
            throw \Illuminate\Validation\ValidationException::withMessages(['courseId' => 'Khóa học không còn nhận bài thi.']);
        }
        $submission = app(\App\Services\RegistrationFields::class)->submission($request, $course);
        $rules = ['selectedAnswers' => ['required', 'array']];
        foreach ($course->questions as $question) {
            $rules['selectedAnswers.'.$question->id] = ['required', 'integer', \Illuminate\Validation\Rule::in($question->answers->pluck('id')->all())];
        }
        $request->validate($rules, ['required' => 'Vui lòng trả lời đầy đủ các câu hỏi.', 'in' => 'Đáp án không hợp lệ.']);
        $values = $submission['values'];
        $request->merge([
            'fullname' => $values['fullname'], 'email' => $values['email'],
            'phone' => $values['phone'] ?? '', 'name_school' => $values['name_school'] ?? '',
            'province' => $values['province'] ?? null, 'school' => null,
            'you_is' => $values['you_is'] ?? '', 'major' => $values['major'] ?? '',
        ]);
        return [$course, $submission['snapshot']];
    }

    public function submitFormCertification(Request $request)
    {
        try {
            [$course, $registrationData] = $this->prepareRegistration($request);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json(['status' => '422', 'msg' => collect($exception->errors())->flatten()->first(), 'errors' => $exception->errors()]);
        }
        $lock = Cache::lock($request->phone . $request->email, 10);

        if (!$lock->get()) {
            return response()->json([
                'status' => '500',
                'msg' => 'Bạn thao tác quá nhanh'
            ]);
        }

        try {
            $course = CertificationTest::find($request->courseId);

            if (!$course) {
                return response()->json([
                    'status' => '500',
                    'msg' => 'Khóa học không tồn tại'
                ]);
            }

            $personSpamPhone = $request->phone ? $course->persons()->where('phone', $request->phone)->count() : 0;
            $personSpamEmail = $course->persons()->where('email', $request->email)->count();

            if ($personSpamPhone > 2 || $personSpamEmail > 2) {
                return response()->json([
                    'status' => '500',
                    'msg' => 'Bạn đã làm bài quiz quá 3 lần!'
                ]);
            }
            
            $totalPoint = collect($course->questions)->sum(function ($question) use ($request) {
                return $question->answer_correct_id == $request->selectedAnswers[$question->id] ? 1 : 0;
            });

            $personCertification = PersonCertificationTest::create([
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'email' => $request->email,
                'name_school' =>  $request->name_school,
                'you_is' =>  $request->you_is,
                'point' => $totalPoint,
                'answer_json' => json_encode($request->selectedAnswers),
                'school_id' => $request->school,
                'province_id' => $request->province,
                'certification_test_id' => $request->courseId,
                'registration_data' => $registrationData,
            ]);

            if ($personCertification->isPass()) {
                try {
                    SendCertificationMailJob::dispatch($personCertification);
                } catch (\Throwable $th) {
                    $personCertification->status_send_mail = 500;
                    $personCertification->msg_send_mail = 'SendMail dispatch error';
                    $personCertification->save();
                    \Log::error('Cannot dispatch certificate email', ['person_id' => $personCertification->id, 'exception' => $th->getMessage()]);
                }
            }

            $personCertification->refresh();
            $personCertification->isPass = $personCertification->isPass();

            return response()->json([
                'status' => '201',
                'msg' => 'create success',
                'data' => $personCertification
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500',
                'msg' => 'Có lỗi xảy ra'
            ]);
        } finally {
            $lock->release();
        }

    }

    public function submitFormCertificationGame(Request $request)
    {
        try {
            [$course, $registrationData] = $this->prepareRegistration($request);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json(['status' => '422', 'msg' => collect($exception->errors())->flatten()->first(), 'errors' => $exception->errors()]);
        }
        $lock = Cache::lock($request->phone . $request->email, 10);

        if (!$lock->get()) {
            return response()->json([
                'status' => '500',
                'msg' => 'procesing'
            ]);
        }

        try {
            $course = CertificationTest::find($request->courseId);

            if (!$course) {
                return response()->json([
                    'status' => '404',
                    'msg' => 'course not found'
                ]);
            }
            
            $totalPoint = collect($course->questions)->sum(function ($question) use ($request) {
                return $question->answer_correct_id == $request->selectedAnswers[$question->id] ? 1 : 0;
            });

            $personCertification = PersonGameCertificationTest::create([
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'email' => $request->email,
                'name_school' =>  $request->name_school,
                
                'you_is' =>  $request->you_is,
                'major' => $request->major,
                'point' => $totalPoint,
                'answer_json' => json_encode($request->selectedAnswers),
                'school_id' => $request->school,
                'province_id' => $request->province,
                'certification_test_id' => $request->courseId,
                'registration_data' => $registrationData,
            ]);

            $personCertification->isPass = $personCertification->isPass();

            return response()->json([
                'status' => 201,
                'msg' => 'create success',
                'data' => $personCertification
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500',
                'msg' => $th->getMessage()
            ]);
        } finally {
            $lock->release();
        }

    }



   /**
     * Quét và gửi lại email cho những bản ghi có status_send_mail = 500
     */
    // public function resendFailedEmails(Request $request)
    // {
    //     $request->validate([
    //         'khoa_id' => 'sometimes|integer',
    //         'limit' => 'sometimes|integer|min:1|max:100'
    //     ]);

    //     try {
    //         $email = $request->email;
    //         // echo $email;
    //         $query = PersonCertificationTest::with(['school', 'province', 'certification'])
    //             ->where('status_send_mail', 500)
    //             ->where('email', $email);
    //             // ->get();
    //             // ->where('certification_test_id', $request->khoa_id)->get();
    //             // ->whereDate('created_at', Carbon::today()->toDateString())
    //             // ->first();

    //         // Lọc theo khoa_id nếu có
    //         // if ($request->has('khoa_id')) {
    //         //     $query->where('certification_test_id', $request->khoa_id)->get();
    //         // }
    //         // dd($query);
    //         // Giới hạn số lượng bản ghi xử lý
    //         $limit = $request->get('limit', 50);
    //         $failedRecords = $query->limit($limit)->get();
    //         // dd($failedRecords);
    //         if ($failedRecords->isEmpty()) {
    //             return response()->json([
    //                 'status' => '200',
    //                 'msg' => 'Không có bản ghi nào cần gửi lại email',
    //                 'data' => [
    //                     'total_processed' => 0,
    //                     'success_count' => 0,
    //                     'failed_count' => 0,
    //                     'results' => []
    //                 ]
    //             ]);
    //         }

    //         $successCount = 0;
    //         $failedCount = 0;
    //         $results = [];

    //         foreach ($failedRecords as $personCertification) {
    //             try {
    //                 // Kiểm tra xem có pass không
    //                 if (!$personCertification->isPass()) {
    //                     $results[] = [
    //                         'email' => $personCertification->email,
    //                         'status' => 'skipped',
    //                         'reason' => 'Chưa đạt điểm để nhận chứng chỉ'
    //                     ];
    //                     continue;
    //                 }

    //                 // Gửi mail
    //                 $this->sendMailToPerson($personCertification);

    //                 // Cập nhật trạng thái thành công
    //                 $personCertification->status_send_mail = 200;
    //                 $personCertification->msg_send_mail = 'SendMail Success - Resent';
    //                 $personCertification->save();

    //                 $successCount++;
    //                 $results[] = [
    //                     'email' => $personCertification->email,
    //                     'fullname' => $personCertification->fullname,
    //                     'status' => 'success',
    //                     'reason' => 'Email đã được gửi thành công'
    //                 ];

    //             } catch (\Throwable $th) {
    //                 // Cập nhật trạng thái lỗi
    //                 $personCertification->status_send_mail = 500;
    //                 $personCertification->msg_send_mail = 'SendMail error (retry): ' . $th->getMessage();
    //                 $personCertification->save();

    //                 $failedCount++;
    //                 $results[] = [
    //                     'email' => $personCertification->email,
    //                     'fullname' => $personCertification->fullname,
    //                     'status' => 'failed',
    //                     'reason' => 'Lỗi khi gửi email: ' . $th->getMessage()
    //                 ];

    //                 \Log::error('Resend mail error for ' . $personCertification->email . ': ' . $th->getMessage());
    //             }
    //         }

    //         return response()->json([
    //             'status' => '200',
    //             'msg' => 'Hoàn thành quá trình gửi lại email',
    //             'data' => [
    //                 'total_processed' => $failedRecords->count(),
    //                 'success_count' => $successCount,
    //                 'failed_count' => $failedCount,
    //                 'results' => $results
    //             ]
    //         ]);

    //     } catch (\Throwable $th) {
    //         \Log::error('Resend failed emails error: ' . $th->getMessage());
    //         return response()->json([
    //             'status' => '500',
    //             'msg' => 'Có lỗi xảy ra khi thực hiện quá trình gửi lại email',
    //             'error' => $th->getMessage()
    //         ]);
    //     }
    // }
   /**
     * Helper method để gửi mail cho một person
     */
    // private function sendMailToPerson($personCertification)
    // {
    //     if ($personCertification->certification_test_id == '23') {
    //         Mail::to($personCertification->email)->send(new SendCertificationMailV2([
    //             'name' => $personCertification->fullname,
    //             'school' => $personCertification->school->school,
    //             'province' => $personCertification->province->province,
    //             'course' => $personCertification->certification->course,
    //             "title" => $personCertification->certification->title,
    //             "content" => $personCertification->certification->content,
    //             'subject' => $personCertification->certification->subject,
    //         ]));
    //     } else {
    //         Mail::to($personCertification->email)->send(new SendCertificationMail([
    //             'name' => $personCertification->fullname,
    //             'school' => $personCertification->school->school,
    //             'province' => $personCertification->province->province,
    //             'course' => $personCertification->certification->course,
    //             "title" => $personCertification->certification->title,
    //             "content" => $personCertification->certification->content,
    //             'subject' => $personCertification->certification->subject,
    //         ]));
    //     }
    // }

}
