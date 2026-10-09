<?php

namespace Tests\Feature;

use App\Mail\SendCertificationMail;
use App\Models\CertificateTemplate;
use App\Models\CertificationTest;
use App\Models\User;
use App\Services\CertificateEmail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CertificateEmailContentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_email_uses_the_selected_certificate_and_the_unit_saved_in_the_form(): void
    {
        $course = new CertificationTest(['course' => 'Khóa AI', 'title' => 'Tiêu đề cũ']);
        $course->setRelation('certificateTemplate', new CertificateTemplate(['name' => 'TEST-AI', 'layout' => 'organization']));
        $mail = new SendCertificationMail([
            'name' => 'NGUYỄN VĂN AN', 'course' => $course->course, 'title' => 'Tiêu đề cũ', 'certification' => $course,
            'completed_date' => '09/10/2026',
            'registration_data' => ['custom_generated_key' => ['label' => 'Đơn vị', 'value' => 'Đoàn phường Diên Hồng']],
            'subject' => '[cer] - [unit] - [name]',
        ]);
        $this->assertSame('TEST-AI - Đoàn phường Diên Hồng - NGUYỄN VĂN AN', $mail->envelope()->subject);
        $body = $mail->content()->with['htmlContent'];
        foreach (['NGUYỄN VĂN AN', 'Khóa AI', 'TEST-AI', 'Đoàn phường Diên Hồng', '09/10/2026', 'Chứng chỉ PDF được đính kèm'] as $value) {
            $this->assertStringContainsString($value, $body);
        }
        $this->assertStringNotContainsString('Tiêu đề cũ', $body);
        $this->assertStringNotContainsString('[unit]', $body);
    }

    public function test_custom_content_is_kept_and_submitted_values_are_escaped_without_recursive_replacement(): void
    {
        $info = ['name' => 'Người [course] <b>An</b>', 'course' => 'AI & dữ liệu', 'organization' => '<script>evil</script>'];
        $body = CertificateEmail::render('<p>[name] / [unit] / [course]</p>', $info, true);
        $this->assertSame('<p>Người [course] &lt;b&gt;An&lt;/b&gt; / &lt;script&gt;evil&lt;/script&gt; / AI &amp; dữ liệu</p>', $body);
        $this->assertSame('Người [course] <b>An</b> AI & dữ liệu', CertificateEmail::render("[name]\r\n[course]", $info));
        $mail = new SendCertificationMail($info + ['subject' => 'Thư riêng [name]', 'content' => '<p>Nội dung riêng [unit]</p>']);
        $this->assertSame('<p>Nội dung riêng &lt;script&gt;evil&lt;/script&gt;</p>', $mail->content()->with['htmlContent']);
    }

    public function test_course_email_editor_shows_template_variables_and_preserves_saved_content(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->get(route('certification.create'))->assertOk()
            ->assertSee('Dùng mẫu email chứng chỉ')->assertSee('[unit]')->assertSee('[completed_date]')
            ->assertSee(config('certificates.email_subject'));
        $course = CertificationTest::create([
            'course' => 'Email riêng', 'slug' => 'email-editor-test', 'passing_score' => 1,
            'subject' => 'Tiêu đề riêng [name]', 'content' => '<p>Nội dung riêng [unit]</p>',
        ]);
        $this->get(route('certification.edit', $course))->assertOk()
            ->assertSee('Tiêu đề riêng [name]')->assertSee('Nội dung riêng [unit]');
    }
}
