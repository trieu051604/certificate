<?php

namespace Tests\Feature;

use App\Mail\SendCertificationMail;
use App\Mail\SendCertificationMailV2;
use App\Models\CertificateTemplate;
use App\Models\CertificationTest;
use App\Models\User;
use App\Services\CertificatePdf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CourseCertificateSelectionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(new User(['type' => 'ADMIN']));
    }

    private function template(): CertificateTemplate
    {
        return CertificateTemplate::create([
            'name' => 'Chứng chỉ lựa chọn để kiểm thử',
            'background' => 'css/submit-certification/cer1.jpg',
            'width' => 2048,
            'height' => 1448,
        ]);
    }

    private function payload(CertificateTemplate $template): array
    {
        return [
            'course' => 'Khóa học kiểm thử lựa chọn chứng chỉ',
            'passing_score' => 2,
            'status' => 'ACTIVE',
            'certificate_template_id' => $template->id,
            'subject' => 'Chúc mừng [name]',
            'content' => '<p>Chúc mừng hoàn thành [course]</p>',
        ];
    }

    public function test_create_and_edit_forms_show_the_library_and_remember_the_saved_choice(): void
    {
        $template = $this->template();
        $this->get(route('certification.create'))->assertOk()
            ->assertSee('name="certificate_template_id"', false)->assertSee($template->name)
            ->assertDontSee('name="title"', false);

        $course = CertificationTest::create(array_merge($this->payload($template), ['slug' => 'selection-test']));
        $this->get(route('certification.edit', $course))->assertOk()
            ->assertSee('value="'.$template->id.'" data-image="'.asset($template->background).'" selected', false)
            ->assertDontSee('name="title"', false);
    }

    public function test_course_creation_and_update_save_the_template_and_its_name(): void
    {
        $template = $this->template();
        $payload = $this->payload($template);
        $this->post(route('certification.store'), $payload)->assertRedirect(route('certification.index'));
        $course = CertificationTest::where('course', $payload['course'])->latest('id')->firstOrFail();
        $this->assertEquals($template->id, $course->certificate_template_id);
        $this->assertSame($template->name, $course->title);
        $this->assertSame($template->background, $course->certificateBackground());

        $otherTemplate = $this->template();
        $otherTemplate->update(['name' => 'Chứng chỉ thay thế']);
        $this->put(route('certification.update', $course), $this->payload($otherTemplate))->assertRedirect();
        $course->refresh();
        $this->assertEquals($otherTemplate->id, $course->certificate_template_id);
        $this->assertSame('Chứng chỉ thay thế', $course->title);
    }

    public function test_missing_deleted_and_unknown_templates_are_rejected(): void
    {
        $template = $this->template();
        $payload = $this->payload($template);
        $template->delete();
        $count = CertificationTest::count();
        $this->post(route('certification.store'), $payload)->assertSessionHasErrors('certificate_template_id');
        $payload['certificate_template_id'] = '';
        $this->post(route('certification.store'), $payload)->assertSessionHasErrors('certificate_template_id');
        $payload['certificate_template_id'] = 999999999;
        $this->post(route('certification.store'), $payload)->assertSessionHasErrors('certificate_template_id');
        $this->assertSame($count, CertificationTest::count());
    }

    public function test_templates_selected_by_courses_are_protected_from_deletion(): void
    {
        $template = $this->template();
        CertificationTest::create(array_merge($this->payload($template), ['slug' => 'protected-template-test']));
        $this->delete(route('admin.certificate-templates.destroy', $template))->assertSessionHas('error');
        $this->assertNotSoftDeleted($template);
        $this->get(route('admin.certificate-templates.index'))->assertSee('Được chọn cho 1 khóa học');
    }

    public function test_pdf_uses_the_chosen_background_and_prints_the_course_name(): void
    {
        $template = $this->template();
        $course = CertificationTest::create(array_merge($this->payload($template), ['slug' => 'pdf-selection-test', 'title' => $template->name]));
        $info = ['name' => 'Nguyễn Văn An', 'school' => 'Trường thử', 'province' => 'Gia Lai',
            'course' => $course->course, 'title' => $course->title, 'certification' => $course];
        $pdf = app(CertificatePdf::class)->make($info);
        $html = $pdf->getDomPDF()->getDom()->saveHTML();
        $expectedBackground = 'file://'.str_replace('\\', '/', public_path($template->background));
        $this->assertStringContainsString($expectedBackground, $html);
        $this->assertStringNotContainsString($template->name, $html);
        $output = $pdf->output();
        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertStringContainsString('/Subtype /Image', $output);
        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_legacy_courses_keep_the_default_background(): void
    {
        $course = new CertificationTest(['course' => 'Khóa cũ', 'title' => 'Tiêu đề cũ']);
        $this->assertSame(config('certificates.default_background'), $course->certificateBackground());
    }

    public function test_both_email_variants_use_the_shared_pdf_renderer_without_sending_email(): void
    {
        $course = new CertificationTest(['course' => 'Khóa kiểm thử']);
        $info = ['certification' => $course, 'course' => $course->course];
        $renderer = $this->mock(CertificatePdf::class);
        $document = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $renderer->shouldReceive('make')->with($info)->twice()->andReturn($document);
        $this->assertCount(1, (new SendCertificationMail($info))->attachments());
        $this->assertCount(1, (new SendCertificationMailV2($info))->attachments());
    }
}
