<?php

namespace Tests\Feature;

use App\Mail\SendCertificationMail;
use App\Models\AnswerCertificationTest;
use App\Models\CertificateTemplate;
use App\Models\CertificationTest;
use App\Models\PersonCertificationTest;
use App\Models\QuestionCertificationTest;
use App\Models\RegistrationForm;
use App\Services\RegistrationFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AutomaticCertificateMailTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['queue.default' => 'sync']);
        Mail::fake();
    }

    private function payload(bool $passing = true, bool $withUnit = false): array
    {
        $fields = array_slice(RegistrationFields::defaults(), 0, 2);
        if ($withUnit) {
            $fields[] = ['key' => 'custom_unit', 'label' => 'Đơn vị', 'type' => 'text', 'required' => true, 'placeholder' => '', 'options' => []];
        }
        $form = RegistrationForm::create(['name' => 'Form kiểm thử mail', 'fields' => $fields]);
        $template = CertificateTemplate::firstOrFail();
        $course = CertificationTest::create([
            'course' => 'Khóa kiểm thử gửi chứng chỉ', 'slug' => 'auto-certificate-mail-test',
            'status' => 'ACTIVE', 'passing_score' => 1, 'registration_form_id' => $form->id,
            'certificate_template_id' => $template->id, 'title' => $template->name,
        ]);
        $question = QuestionCertificationTest::create(['question' => 'Chọn đáp án đúng', 'answer_correct_id' => 0, 'certification_test_id' => $course->id]);
        $right = AnswerCertificationTest::create(['answer' => 'Đúng', 'question_certification_test_id' => $question->id]);
        $wrong = AnswerCertificationTest::create(['answer' => 'Sai', 'question_certification_test_id' => $question->id]);
        $question->update(['answer_correct_id' => $right->id]);
        return [
            'courseId' => $course->id,
            'registration' => ['fullname' => 'Nguyễn Văn An', 'email' => 'auto-mail-test@example.test'] + ($withUnit ? ['custom_unit' => 'Đoàn phường Diên Hồng'] : []),
            'selectedAnswers' => [$question->id => $passing ? $right->id : $wrong->id],
        ];
    }

    public function test_passing_submission_automatically_sends_email_to_the_registered_address(): void
    {
        $response = $this->postJson('/api/certification/submit-form', $this->payload())
            ->assertOk()->assertJsonPath('status', '201')->assertJsonPath('data.isPass', true)->assertJsonPath('data.status_send_mail', 200);
        Mail::assertSent(SendCertificationMail::class, fn ($mail) => $mail->hasTo('auto-mail-test@example.test'));
        Mail::assertSent(SendCertificationMail::class, 1);
        $this->assertSame('SendMail Success', PersonCertificationTest::findOrFail($response->json('data.id'))->msg_send_mail);
    }

    public function test_failed_submission_does_not_send_a_certificate_or_claim_email_success(): void
    {
        $this->postJson('/api/certification/submit-form', $this->payload(false))
            ->assertJsonPath('status', '201')->assertJsonPath('data.isPass', false)->assertJsonPath('data.status_send_mail', null);
        Mail::assertNothingSent();
    }

    public function test_mail_failure_is_recorded_without_losing_the_passing_result(): void
    {
        Mail::shouldReceive('to')->once()->with('auto-mail-test@example.test')->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Simulated SMTP failure'));
        $response = $this->postJson('/api/certification/submit-form', $this->payload())
            ->assertJsonPath('status', '201')->assertJsonPath('data.isPass', true)->assertJsonPath('data.status_send_mail', 500);
        $person = PersonCertificationTest::findOrFail($response->json('data.id'));
        $this->assertTrue($person->isPass());
        $this->assertSame('SendMail error', $person->msg_send_mail);
    }

    public function test_queue_dispatch_failure_is_recorded_without_losing_the_passing_result(): void
    {
        Bus::shouldReceive('dispatch')->once()->andThrow(new \RuntimeException('Simulated queue failure'));
        $this->postJson('/api/certification/submit-form', $this->payload())
            ->assertJsonPath('status', '201')->assertJsonPath('data.isPass', true)->assertJsonPath('data.status_send_mail', 500)
            ->assertJsonPath('data.msg_send_mail', 'SendMail dispatch error');
        Mail::assertNothingSent();
    }

    public function test_automatic_email_has_default_content_and_a_real_pdf_attachment_without_using_smtp(): void
    {
        // Build the complete email locally, including PDF bytes, without contacting SMTP.
        config(['mail.default' => 'array', 'mail.from.address' => 'sender@example.test', 'mail.from.name' => 'Certificate Test']);
        Mail::swap(new MailManager($this->app));
        $this->postJson('/api/certification/submit-form', $this->payload())
            ->assertJsonPath('status', '201')->assertJsonPath('data.status_send_mail', 200);
        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $message = $messages->first()->getOriginalMessage();
        $this->assertSame('auto-mail-test@example.test', $message->getTo()[0]->getAddress());
        $this->assertStringContainsString('NGUYỄN VĂN AN', $message->getSubject());
        $this->assertStringContainsString('Chứng chỉ PDF được đính kèm', $message->getHtmlBody());
        $this->assertCount(1, $message->getAttachments());
        $attachment = $message->getAttachments()[0];
        $this->assertSame('certification.pdf', $attachment->getFilename());
        $this->assertStringStartsWith('%PDF-', $attachment->getBody());
    }

    public function test_custom_course_email_has_matching_unit_template_date_and_pdf_attachment(): void
    {
        config(['mail.default' => 'array', 'mail.from.address' => 'sender@example.test', 'mail.from.name' => 'Certificate Test']);
        Mail::swap(new MailManager($this->app));
        $payload = $this->payload(true, true);
        $template = CertificateTemplate::create(['name' => 'TEST-AI kiểm thử', 'layout' => 'organization', 'background' => 'css/submit-certification/cer1.jpg', 'width' => 2048, 'height' => 1448]);
        CertificationTest::findOrFail($payload['courseId'])->update([
            'certificate_template_id' => $template->id,
            'subject' => '[cer] - [unit] - [completed_date]',
            'content' => config('certificates.email_content'),
        ]);
        $response = $this->postJson('/api/certification/submit-form', $payload)->assertJsonPath('data.status_send_mail', 200);
        $person = PersonCertificationTest::findOrFail($response->json('data.id'));
        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $message = $messages->first()->getOriginalMessage();
        $this->assertSame('TEST-AI kiểm thử - Đoàn phường Diên Hồng - '.$person->created_at->format('d/m/Y'), $message->getSubject());
        $this->assertStringContainsString('Đoàn phường Diên Hồng', $message->getHtmlBody());
        $this->assertStringContainsString('TEST-AI kiểm thử', $message->getHtmlBody());
        $this->assertStringContainsString('NGUYỄN VĂN AN', $message->getHtmlBody());
        $this->assertStringNotContainsString('[unit]', $message->getHtmlBody());
        $this->assertCount(1, $message->getAttachments());
        $this->assertStringStartsWith('%PDF-', $message->getAttachments()[0]->getBody());
    }
}
