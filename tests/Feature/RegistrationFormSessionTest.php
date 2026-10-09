<?php

namespace Tests\Feature;

use App\Models\RegistrationForm;
use App\Models\User;
use App\Services\RegistrationFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegistrationFormSessionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Exercise the real CSRF middleware, which normally skips PHPUnit requests.
        $this->app['env'] = 'local';
    }

    private function draft(): array
    {
        return ['name' => 'Form chưa lưu', 'fields_json' => json_encode(RegistrationFields::defaults(), JSON_UNESCAPED_UNICODE)];
    }

    public function test_stale_csrf_token_restores_new_form_without_creating_a_record(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $count = RegistrationForm::count();
        $input = $this->draft();
        $this->withSession(['_token' => 'current-token'])->post(route('registration-forms.store'), ['_token' => 'stale-token'] + $input)
            ->assertRedirect(route('registration-forms.create', ['recover' => 1]));
        $this->assertSame($count, RegistrationForm::count());
        $this->get(route('registration-forms.create', ['recover' => 1]))->assertOk()->assertSee('Form chưa lưu')->assertSee('Thông tin bạn nhập đã được khôi phục')
            ->assertSessionHas('_old_input', $input)->assertSessionMissing('registration_form_recovery');
        $this->post(route('registration-forms.store'), ['_token' => 'current-token'] + $input)->assertRedirect(route('registration-forms.index'));
        $this->assertSame($count + 1, RegistrationForm::count());
    }

    public function test_stale_update_preserves_draft_and_does_not_change_saved_form(): void
    {
        $form = RegistrationForm::create(['name' => 'Tên đã lưu', 'fields' => RegistrationFields::defaults()]);
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->withSession(['_token' => 'current-token'])->put(route('registration-forms.update', $form), ['_token' => 'stale-token'] + $this->draft())
            ->assertRedirect(route('registration-forms.edit', ['registration_form' => $form->id, 'recover' => 1]));
        $this->assertSame('Tên đã lưu', $form->fresh()->name);
        $this->get(route('registration-forms.edit', ['registration_form' => $form->id, 'recover' => 1]))
            ->assertOk()->assertSee('Form chưa lưu')->assertSee('Thông tin bạn nhập đã được khôi phục');
    }

    public function test_expired_session_requires_login_and_keeps_draft_across_redirect(): void
    {
        $count = RegistrationForm::count();
        $this->post(route('registration-forms.store'), ['_token' => 'expired-token'] + $this->draft())
            ->assertRedirect(route('registration-forms.create', ['recover' => 1]));
        $this->get(route('registration-forms.create', ['recover' => 1]))->assertRedirect(route('login'));
        $this->assertSame($count, RegistrationForm::count());
        $this->assertSame('Form chưa lưu', session('registration_form_recovery.input.name'));
        $this->assertSame(route('registration-forms.create', ['recover' => 1]), session('url.intended'));
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->get(route('registration-forms.create', ['recover' => 1]))->assertOk()->assertSee('Form chưa lưu');
    }

    public function test_json_requests_and_other_admin_forms_still_reject_invalid_csrf(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->withSession(['_token' => 'current-token'])->postJson(route('registration-forms.store'), ['_token' => 'stale-token'] + $this->draft())->assertStatus(419);
        $this->post(route('admin.certificate-templates.store'), ['_token' => 'stale-token', 'name' => 'Invalid'])->assertStatus(419);
        $this->assertNull(session('registration_form_recovery'));
    }
}
