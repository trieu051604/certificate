<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\CertificateLookupCode;
use App\Models\CertificationTest;
use App\Models\PersonCertificationTest;
use App\Services\PublicCertificate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PublicCertificationController extends Controller
{
    public function index()
    {
        $courses = CertificationTest::where('type', 'CERTIFICATION')->where('status', 'ACTIVE')
            ->where(fn ($query) => $query->whereNull('date_expire')->orWhereDate('date_expire', '>=', now()->toDateString()))
            ->whereHas('questions')->orderByDesc('id')->get(['id', 'slug', 'course', 'date_expire']);
        return response()->json(['data' => $courses]);
    }

    public function show(string $slug)
    {
        $course = CertificationTest::with(['registrationForm', 'questions.answers', 'certificateTemplate'])
            ->where('slug', $slug)->where('type', 'CERTIFICATION')->where('status', 'ACTIVE')->firstOrFail();
        abort_if($course->date_expire && Carbon::parse($course->date_expire)->endOfDay()->isPast(), 410, 'Khóa học đã hết hạn làm bài.');
        abort_if($course->questions->isEmpty(), 409, 'Khóa học chưa có câu hỏi.');
        $fields = $course->registrationFields();
        return response()->json(['data' => [
            'id' => $course->id, 'slug' => $course->slug, 'course' => $course->course,
            'passing_score' => (int) $course->passing_score, 'date_expire' => $course->date_expire,
            'registration_form_name' => $course->registrationForm?->name,
            'registration_fields' => $fields,
            'provinces' => collect($fields)->contains('type', 'province')
                ? DB::table('province_certification')->orderBy('province')->get(['id', 'province']) : [],
            'certificate' => [
                'name' => $course->certificateTemplate?->name ?? $course->title,
                'background_url' => url($course->certificateBackground()),
            ],
            // Never serialize the Question model: it also contains answer_correct_id.
            'questions' => $course->questions->map(fn ($question) => [
                'id' => $question->id, 'question' => $question->question,
                'answers' => $question->answers->map(fn ($answer) => ['id' => $answer->id, 'answer' => $answer->answer])->values(),
            ])->values(),
        ]])->header('Cache-Control', 'no-store');
    }

    private function email(Request $request): string
    {
        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);
        return strtolower(trim($validated['email']));
    }

    private function lookupKey(string $email): string
    {
        return 'certificate-lookup:'.hash('sha256', $email);
    }

    private function passedStudents(string $email)
    {
        return PersonCertificationTest::with(['certification.certificateTemplate'])
            ->whereRaw('LOWER(email) = ?', [$email])->whereHas('certification')
            ->orderByDesc('id')->get()->filter(fn ($student) => $student->isPass())->values();
    }

    public function requestCode(Request $request)
    {
        $email = $this->email($request);
        $key = $this->lookupKey($email);
        // Limit each address as well as the route's IP throttle, without exposing account existence.
        abort_unless(Cache::add($key.':cooldown', true, 60), 429, 'Vui lòng chờ một phút trước khi yêu cầu mã mới.');
        Cache::forget($key);
        if ($this->passedStudents($email)->isNotEmpty()) {
            $code = (string) random_int(100000, 999999);
            Cache::put($key, ['hash' => Hash::make($code), 'attempts' => 0, 'expires_at' => now()->addMinutes(10)->timestamp], now()->addMinutes(10));
            try {
                Mail::to($email)->send(new CertificateLookupCode($code));
            } catch (\Throwable $exception) {
                Cache::forget($key);
                \Log::error('Cannot send certificate lookup code', ['exception' => $exception->getMessage()]);
                return response()->json(['message' => 'Chưa gửi được mã xác minh. Vui lòng thử lại sau.'], 503);
            }
        }
        return response()->json(['message' => 'Nếu email có chứng chỉ đã đạt, mã xác minh sẽ được gửi đến hộp thư của bạn.'])->header('Cache-Control', 'no-store');
    }

    public function verifyCode(Request $request)
    {
        $email = $this->email($request);
        $request->validate(['code' => ['required', 'string', 'regex:/\A[0-9]{6}\z/']]);
        $key = $this->lookupKey($email);
        $lock = Cache::lock($key.':lock', 10);
        abort_unless($lock->get(), 429, 'Vui lòng thử lại sau.');
        try {
            $challenge = Cache::get($key);
            if (!$challenge || $challenge['expires_at'] <= now()->timestamp || $challenge['attempts'] >= 5 || !Hash::check($request->code, $challenge['hash'])) {
                if ($challenge) {
                    $challenge['attempts']++;
                    if ($challenge['attempts'] >= 5) Cache::forget($key);
                    else Cache::put($key, $challenge, Carbon::createFromTimestamp($challenge['expires_at']));
                }
                throw ValidationException::withMessages(['code' => 'Mã không đúng hoặc đã hết hạn.']);
            }
            Cache::forget($key); // One-time challenge, not a permanent download credential.
            return response()->json(['data' => $this->passedStudents($email)->map(fn ($student) => PublicCertificate::result($student))])
                ->header('Cache-Control', 'private, no-store');
        } finally {
            $lock->release();
        }
    }
}
