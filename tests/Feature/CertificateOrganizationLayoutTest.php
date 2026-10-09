<?php

namespace Tests\Feature;

use App\Mail\SendCertificationMail;
use App\Models\AnswerCertificationTest;
use App\Models\CertificateTemplate;
use App\Models\CertificationTest;
use App\Models\QuestionCertificationTest;
use App\Models\RegistrationForm;
use App\Models\User;
use App\Services\CertificatePdf;
use App\Services\RegistrationFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CertificateOrganizationLayoutTest extends TestCase
{
    use DatabaseTransactions;

    private function course(): CertificationTest
    {
        $template = CertificateTemplate::create([
            'name' => 'Mẫu kiểm thử Đơn vị', 'background' => 'css/submit-certification/cer1.jpg',
            'width' => 2048, 'height' => 1448, 'layout' => 'organization',
        ]);
        return CertificationTest::create([
            'course' => 'Ứng dụng AI trong học tập, công việc và hoạt động Đoàn – Hội – Đội',
            'slug' => 'test-unit-layout', 'passing_score' => 1, 'status' => 'ACTIVE',
            'certificate_template_id' => $template->id,
        ]);
    }

    public function test_pdf_maps_the_saved_unit_snapshot_and_has_no_school_or_province_rows(): void
    {
        $course = $this->course();
        $pdf = app(CertificatePdf::class)->make([
            'name' => 'Nguyễn Văn An', 'school' => 'Trường không được dùng', 'province' => 'Tỉnh không được dùng',
            'course' => $course->course, 'title' => 'Tiêu đề chứng chỉ không phải tên khóa', 'certification' => $course,
            'registration_data' => ['custom_generated_key' => ['label' => 'Đơn vị', 'value' => 'Đoàn Thanh niên Diên Hồng']],
        ]);
        $html = html_entity_decode($pdf->getDomPDF()->getDom()->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $this->assertStringContainsString('certificate-organization', $html);
        $this->assertStringContainsString('NGUYỄN VĂN AN', $html);
        $this->assertStringContainsString('Đoàn Thanh niên Diên Hồng', $html);
        $this->assertStringContainsString($course->course, $html);
        $this->assertStringNotContainsString('class="truong"', $html);
        $this->assertStringNotContainsString('class="tinh"', $html);
        $this->assertStringNotContainsString('Trường không được dùng', $html);
        $this->assertStringNotContainsString('Tiêu đề chứng chỉ không phải tên khóa', $html);
        $this->assertStringStartsWith('%PDF-', $pdf->output());
        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_custom_unit_is_forwarded_to_both_download_and_email_attachment(): void
    {
        Mail::fake();
        $course = $this->course();
        $form = RegistrationForm::create(['name' => 'Form Đơn vị kiểm thử', 'fields' => [
            ...array_slice(RegistrationFields::defaults(), 0, 2),
            ['key' => 'custom_random_key', 'label' => 'Đơn vị', 'type' => 'text', 'required' => true, 'placeholder' => '', 'options' => []],
        ]]);
        $course->update(['registration_form_id' => $form->id]);
        $question = QuestionCertificationTest::create(['question' => 'Đúng?', 'answer_correct_id' => 0, 'certification_test_id' => $course->id]);
        $answer = AnswerCertificationTest::create(['answer' => 'Đúng', 'question_certification_test_id' => $question->id]);
        $question->update(['answer_correct_id' => $answer->id]);
        $response = $this->postJson('/api/certification/submit-form', [
            'courseId' => $course->id, 'selectedAnswers' => [$question->id => $answer->id],
            'registration' => ['fullname' => 'Nguyễn Văn An', 'email' => 'unit-test@example.test', 'custom_random_key' => 'Đoàn phường kiểm thử'],
        ])->assertJsonPath('status', '201')->assertJsonPath('data.isPass', true);

        $captured = [];
        $renderer = $this->mock(CertificatePdf::class);
        $renderer->shouldReceive('make')->twice()->andReturnUsing(function ($info) use (&$captured) {
            $captured[] = $info;
            return (new CertificatePdf)->make($info);
        });
        Mail::assertSent(SendCertificationMail::class, function ($mail) {
            $this->assertCount(1, $mail->attachments());
            return true;
        });
        $this->get(route('certification.download', $response->json('data.id')))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertCount(2, $captured);
        foreach ($captured as $info) {
            $this->assertSame('Đoàn phường kiểm thử', $info['registration_data']['custom_random_key']['value']);
        }
    }

    public function test_long_names_units_and_courses_fit_without_overlapping_other_rows(): void
    {
        $course = $this->course();
        $pdf = app(CertificatePdf::class)->make([
            'name' => str_repeat('Nguyễn ', 25), 'school' => '', 'province' => '',
            'course' => str_repeat('Ứng dụng AI ', 18), 'certification' => $course,
            'registration_data' => ['custom_key' => ['label' => 'Đơn vị', 'value' => str_repeat('Đoàn thanh niên ', 16)]],
        ]);
        $boxes = [];
        $pdf->getDomPDF()->setCallbacks([['event' => 'end_frame', 'f' => function ($frame) use (&$boxes) {
            $node = $frame->get_node();
            if ($node instanceof \DOMElement && in_array($node->getAttribute('class'), ['certificate-name', 'certificate-unit', 'certificate-course'], true)) {
                $boxes[$node->getAttribute('class')] = $frame->get_border_box();
            }
        }]]);
        $pdf->output();
        $this->assertCount(3, $boxes);
        [$nx, $ny, $nw, $nh] = $boxes['certificate-name'];
        [$ux, $uy, $uw, $uh] = $boxes['certificate-unit'];
        [$cx, $cy, $cw, $ch] = $boxes['certificate-course'];
        $this->assertLessThan($uy, $ny + $nh);
        $this->assertLessThan($cy, $uy + $uh, json_encode($boxes));
        $this->assertLessThan(1536, $cx + $cw);
        $this->assertLessThan(1086 * .75, $cy + $ch);
        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_admin_can_change_layout_and_invalid_layout_is_rejected(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $template = $this->course()->certificateTemplate;
        $this->patch(route('admin.certificate-templates.layout', $template), ['layout' => 'invalid'])->assertSessionHasErrors('layout');
        $this->assertSame('organization', $template->fresh()->layout);
        $this->patch(route('admin.certificate-templates.layout', $template), ['layout' => 'school'])->assertRedirect()->assertSessionHas('success');
        $this->assertSame('school', $template->fresh()->layout);
    }

    public function test_guest_and_non_admin_cannot_change_the_template_layout(): void
    {
        $template = $this->course()->certificateTemplate;
        $url = route('admin.certificate-templates.layout', $template);
        $this->patch($url, ['layout' => 'school'])->assertRedirect(route('login'));
        $this->actingAs(new User(['type' => 'MANAGER']));
        $this->patch($url, ['layout' => 'school'])->assertRedirect();
        $this->assertSame('organization', $template->fresh()->layout);
    }
}
