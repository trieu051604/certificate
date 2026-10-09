<?php

namespace Tests\Feature;

use App\Mail\CertificateLookupCode;
use App\Mail\SendCertificationMail;
use App\Models\AnswerCertificationTest;
use App\Models\CertificationTest;
use App\Models\PersonCertificationTest;
use App\Models\QuestionCertificationTest;
use App\Models\RegistrationForm;
use App\Models\User;
use App\Services\RegistrationFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicCertificationApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void { parent::setUp(); Mail::fake(); }

    private function fixture(): array
    {
        $fields = [...array_slice(RegistrationFields::defaults(), 0, 2), [
            'key' => 'custom_unit', 'label' => 'Đơn vị', 'type' => 'text', 'required' => true, 'placeholder' => '', 'options' => [],
        ]];
        $form = RegistrationForm::create(['name' => 'Form Đơn vị', 'fields' => $fields]);
        $course = CertificationTest::create(['course' => 'API khóa AI', 'slug' => 'api-test-ai', 'passing_score' => 1,
            'status' => 'ACTIVE', 'registration_form_id' => $form->id]);
        $question = QuestionCertificationTest::create(['certification_test_id' => $course->id, 'question' => 'AI giúp làm gì?', 'answer_correct_id' => 0]);
        $right = $question->answers()->create(['answer' => 'Hỗ trợ con người']);
        $wrong = $question->answers()->create(['answer' => 'Thay thế mọi người']);
        $question->update(['answer_correct_id' => $right->id]);
        return [$course, $question, $right, $wrong];
    }

    private function submission(array $fixture, bool $passed = true): array
    {
        [$course, $question, $right, $wrong] = $fixture;
        return ['courseId' => $course->id, 'registration' => [
            'fullname' => 'Người kiểm thử', 'email' => 'learner@example.test', 'custom_unit' => 'Đoàn kiểm thử',
        ], 'selectedAnswers' => [$question->id => $passed ? $right->id : $wrong->id]];
    }

    public function test_course_returns_assigned_form_choices_and_absolute_assets_without_answer_keys(): void
    {
        [$course, $question, $right] = $this->fixture();
        $response = $this->getJson('/api/certification/courses/'.$course->slug)->assertOk()
            ->assertJsonPath('data.registration_form_name', 'Form Đơn vị')
            ->assertJsonPath('data.registration_fields.2.label', 'Đơn vị')
            ->assertJsonPath('data.questions.0.answers.0.id', $right->id);
        $this->assertArrayNotHasKey('answer_correct_id', $response->json('data.questions.0'));
        $this->assertArrayNotHasKey('subject', $response->json('data'));
        $this->assertStringStartsWith('http', $response->json('data.certificate.background_url'));
        $this->get('/certification/'.$course->slug)->assertOk()->assertDontSee('answer_correct_id');
        $this->getJson('/api/certification/courses')->assertOk()->assertJsonFragment(['slug' => $course->slug]);
        $this->actingAs(new User(['type' => 'ADMIN']))->get(route('admin.certification.import', $course))->assertOk()->assertSee('Đáp án đúng');
    }

    public function test_unavailable_courses_are_rejected(): void
    {
        [$course] = $this->fixture();
        $course->update(['date_expire' => now()->subDay()->toDateString()]);
        $this->getJson('/api/certification/courses/'.$course->slug)->assertStatus(410);
        $course->update(['status' => 'UNACTIVE']);
        $this->getJson('/api/certification/courses/'.$course->slug)->assertNotFound();
        $this->getJson('/api/certification/courses/not-found')->assertNotFound();
    }

    public function test_submit_scores_in_backend_and_only_passed_submissions_get_signed_pdf(): void
    {
        $fixture = $this->fixture();
        $response = $this->postJson('/api/certification/submit-form', $this->submission($fixture))->assertOk()
            ->assertJsonPath('data.isPass', true)->assertJsonPath('data.point', 1)
            ->assertJsonPath('data.organization', 'Đoàn kiểm thử');
        $this->assertArrayNotHasKey('answer_json', $response->json('data'));
        $url = $response->json('data.download_url');
        $this->assertStringStartsWith('http', $url);
        $pdf = $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $id = $response->json('data.id');
        $this->getJson('/api/certification/certificates/'.$id.'/download')->assertForbidden();
        $this->get('/certification/download/'.$id)->assertForbidden();
        $this->get(str_replace('/'.$id.'/download', '/'.($id + 1).'/download', $url))->assertForbidden();
        $failed = $this->postJson('/api/certification/submit-form', $this->submission($fixture, false))->assertJsonPath('data.isPass', false)
            ->assertJsonPath('data.point', 0)->assertJsonPath('data.download_url', null);
        Mail::assertSent(SendCertificationMail::class, 1);
        $this->assertSame('Đoàn kiểm thử', PersonCertificationTest::find($id)->registration_data['custom_unit']['value']);
    }

    public function test_lookup_verifies_email_before_returning_certificates_and_code_is_single_use(): void
    {
        $fixture = $this->fixture();
        $this->postJson('/api/certification/submit-form', $this->submission($fixture))->assertJsonPath('data.isPass', true);
        $this->postJson('/api/certification/lookup/request-code', ['email' => 'learner@example.test'])->assertOk()->assertJsonMissingPath('data');
        $code = null;
        Mail::assertSent(CertificateLookupCode::class, function ($mail) use (&$code) { $code = $mail->code; return true; });
        $this->postJson('/api/certification/lookup/verify-code', ['email' => 'other@example.test', 'code' => $code])->assertUnprocessable();
        $this->postJson('/api/certification/lookup/verify-code', ['email' => 'learner@example.test', 'code' => '000000'])->assertUnprocessable();
        $response = $this->postJson('/api/certification/lookup/verify-code', ['email' => 'learner@example.test', 'code' => $code])
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.organization', 'Đoàn kiểm thử');
        $this->get($response->json('data.0.download_url'))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->postJson('/api/certification/lookup/verify-code', ['email' => 'learner@example.test', 'code' => $code])->assertUnprocessable();
        $this->travel(61)->minutes();
        $this->get($response->json('data.0.download_url'))->assertForbidden();
    }

    public function test_lookup_has_no_account_enumeration_and_limits_bad_attempts_and_expiry(): void
    {
        $fixture = $this->fixture();
        $this->postJson('/api/certification/submit-form', $this->submission($fixture));
        $known = $this->postJson('/api/certification/lookup/request-code', ['email' => 'learner@example.test'])->assertOk();
        $unknown = $this->postJson('/api/certification/lookup/request-code', ['email' => 'missing@example.test'])->assertOk();
        $this->assertSame($known->json(), $unknown->json());
        $this->postJson('/api/certification/lookup/request-code', ['email' => 'learner@example.test'])->assertStatus(429);
        $code = Mail::sent(CertificateLookupCode::class)->first()->code;
        for ($i = 0; $i < 5; $i++) $this->postJson('/api/certification/lookup/verify-code', ['email' => 'learner@example.test', 'code' => '000000'])->assertUnprocessable();
        $this->postJson('/api/certification/lookup/verify-code', ['email' => 'learner@example.test', 'code' => $code])->assertUnprocessable();
        $this->travel(2)->minutes();
        $this->postJson('/api/certification/lookup/request-code', ['email' => 'learner@example.test'])->assertOk();
        $code = Mail::sent(CertificateLookupCode::class)->last()->code;
        $this->travel(11)->minutes();
        $this->postJson('/api/certification/lookup/verify-code', ['email' => 'learner@example.test', 'code' => $code])->assertUnprocessable();
    }

    public function test_cors_allows_local_frontend_and_rejects_unknown_origins(): void
    {
        $this->withHeaders(['Origin' => 'http://localhost:5173', 'Access-Control-Request-Method' => 'POST'])
            ->options('/api/certification/submit-form')->assertSuccessful()->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
        $this->withHeaders(['Origin' => 'https://untrusted.example', 'Access-Control-Request-Method' => 'POST'])
            ->options('/api/certification/submit-form')->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
