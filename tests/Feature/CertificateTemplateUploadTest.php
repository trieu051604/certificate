<?php

namespace Tests\Feature;

use App\Models\CertificateTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateTemplateUploadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('certificate_templates');
    }

    public function test_admin_can_upload_a_named_template_and_see_it_in_the_gallery(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $image = UploadedFile::fake()->image('original-certificate.jpg', 120, 80);

        $this->post(route('admin.certificate-templates.store'), ['name' => '  Chứng chỉ kiểm thử  ', 'image' => $image, 'layout' => 'organization'])
            ->assertRedirect(route('admin.certificate-templates.index'))
            ->assertSessionHas('success');

        $template = CertificateTemplate::where('name', 'Chứng chỉ kiểm thử')->firstOrFail();
        $this->assertSame(120, $template->width);
        $this->assertSame('organization', $template->layout);
        $this->assertSame(80, $template->height);
        $this->assertNotSame('certificate-templates/original-certificate.jpg', $template->background);
        Storage::disk('certificate_templates')->assertExists(basename($template->background));
        $this->get(route('admin.certificate-templates.index'))->assertOk()->assertSee('Chứng chỉ kiểm thử')->assertSee('Mẫu đã thêm');
    }

    public function test_guest_and_non_admin_cannot_upload_templates(): void
    {
        $count = CertificateTemplate::count();
        $this->post(route('admin.certificate-templates.store'), [])
            ->assertRedirect(route('login'));

        $this->actingAs(new User(['type' => 'MANAGER']));
        $this->post(route('admin.certificate-templates.store'), [
            'name' => 'Không được lưu',
            'image' => UploadedFile::fake()->image('certificate.png'),
        ])->assertRedirect();

        $this->assertSame($count, CertificateTemplate::count());
        $this->assertSame([], Storage::disk('certificate_templates')->allFiles());
    }

    public function test_blank_name_and_non_image_file_are_rejected_without_writing_files(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $count = CertificateTemplate::count();
        $this->post(route('admin.certificate-templates.store'), [
            'name' => '   ',
            'image' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['name', 'image']);

        $this->assertSame($count, CertificateTemplate::count());
        $this->assertSame([], Storage::disk('certificate_templates')->allFiles());
    }

    public function test_images_with_executable_extensions_are_rejected(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->post(route('admin.certificate-templates.store'), [
            'name' => 'Không được lưu',
            'image' => UploadedFile::fake()->image('certificate.php'),
        ])->assertSessionHasErrors('image');

        $this->assertSame([], Storage::disk('certificate_templates')->allFiles());
    }

    public function test_images_at_five_megabytes_are_accepted(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->post(route('admin.certificate-templates.store'), [
            'name' => 'Ảnh 5 MB',
            'image' => UploadedFile::fake()->image('certificate.png')->size(5120),
        ])->assertRedirect(route('admin.certificate-templates.index'))->assertSessionHas('success');

        $template = CertificateTemplate::where('name', 'Ảnh 5 MB')->firstOrFail();
        $this->assertSame('school', $template->layout);
        Storage::disk('certificate_templates')->assertExists(basename($template->background));
    }

    public function test_images_over_five_megabytes_are_rejected(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->post(route('admin.certificate-templates.store'), [
            'name' => 'Ảnh quá lớn',
            'image' => UploadedFile::fake()->image('certificate.png')->size(5121),
        ])->assertSessionHasErrors('image');

        $this->assertSame([], Storage::disk('certificate_templates')->allFiles());
    }
}
