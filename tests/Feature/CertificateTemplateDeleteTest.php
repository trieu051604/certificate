<?php

namespace Tests\Feature;

use App\Models\CertificateTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateTemplateDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('certificate_templates');
    }

    private function uploadedTemplate(): CertificateTemplate
    {
        $path = UploadedFile::fake()->image('sample.jpg', 120, 80)->store('', 'certificate_templates');

        return CertificateTemplate::create([
            'name' => 'Mẫu thử xóa',
            'background' => 'certificate-templates/'.$path,
            'width' => 120,
            'height' => 80,
        ]);
    }

    public function test_admin_can_delete_an_upload_and_its_image(): void
    {
        $template = $this->uploadedTemplate();
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->delete(route('admin.certificate-templates.destroy', $template))
            ->assertRedirect(route('admin.certificate-templates.index'))->assertSessionHas('success');

        $this->assertSoftDeleted($template);
        Storage::disk('certificate_templates')->assertMissing(basename($template->background));
        $this->get(route('admin.certificate-templates.index'))->assertOk()->assertDontSee('Mẫu thử xóa');
        $this->delete(route('admin.certificate-templates.destroy', $template))->assertNotFound();
    }

    public function test_admin_can_remove_a_bundled_template_from_the_gallery_without_deleting_source_assets(): void
    {
        $template = CertificateTemplate::where('background', 'css/submit-certification/cer1.jpg')->firstOrFail();
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->delete(route('admin.certificate-templates.destroy', $template))->assertSessionHas('success');

        $this->assertSoftDeleted($template);
        $this->assertFileExists(public_path($template->background));
        $this->get(route('admin.certificate-templates.index'))->assertDontSee($template->name);
    }

    public function test_the_active_background_is_protected_even_when_delete_is_requested_directly(): void
    {
        $template = CertificateTemplate::where('background', config('certificates.default_background'))->firstOrFail();
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->delete(route('admin.certificate-templates.destroy', $template))->assertSessionHas('error');

        $this->assertNotSoftDeleted($template);
        $this->assertFileExists(public_path($template->background));
        $this->get(route('admin.certificate-templates.index'))->assertSee($template->name)->assertSee('Đang sử dụng');
    }

    public function test_guest_and_non_admin_cannot_delete_a_template(): void
    {
        $template = $this->uploadedTemplate();
        $this->delete(route('admin.certificate-templates.destroy', $template))->assertRedirect(route('login'));
        $this->actingAs(new User(['type' => 'MANAGER']));
        $this->delete(route('admin.certificate-templates.destroy', $template))->assertRedirect();

        $this->assertNotSoftDeleted($template);
        Storage::disk('certificate_templates')->assertExists(basename($template->background));
    }

    public function test_image_deletion_failure_does_not_remove_the_template_record(): void
    {
        $template = $this->uploadedTemplate();
        $this->actingAs(new User(['type' => 'ADMIN']));
        Storage::shouldReceive('disk')->with('certificate_templates')->once()->andReturnSelf();
        Storage::shouldReceive('delete')->with(basename($template->background))->once()->andReturn(false);
        $this->delete(route('admin.certificate-templates.destroy', $template))->assertStatus(500);

        $this->assertNotSoftDeleted($template);
    }
}
