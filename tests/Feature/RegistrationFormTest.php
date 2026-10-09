<?php

namespace Tests\Feature;

use App\Models\AnswerCertificationTest;
use App\Models\CertificateTemplate;
use App\Models\CertificationTest;
use App\Models\PersonCertificationTest;
use App\Models\PersonGameCertificationTest;
use App\Models\QuestionCertificationTest;
use App\Models\RegistrationForm;
use App\Models\User;
use App\Services\RegistrationFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationFormTest extends TestCase
{
    use DatabaseTransactions;

    private function fields(): array
    {
        return [
            ...array_slice(RegistrationFields::defaults(), 0, 2),
            ['key' => 'custom_department', 'label' => 'Đơn vị công tác', 'type' => 'select', 'required' => true, 'placeholder' => '', 'options' => ['Trường A', 'Trường B']],
            ['key' => 'custom_birthday', 'label' => 'Ngày sinh', 'type' => 'date', 'required' => false, 'placeholder' => '', 'options' => []],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function form(): RegistrationForm
    {
        return RegistrationForm::create(['name' => 'Form dành cho giáo viên', 'fields' => $this->fields()]);
    }

    private function course(?RegistrationForm $form): array
    {
        $course = CertificationTest::create(['course' => 'Khóa học form', 'slug' => 'test-form-course', 'passing_score' => 1, 'status' => 'ACTIVE', 'registration_form_id' => $form?->id]);
        $question = QuestionCertificationTest::create(['question' => 'Câu hỏi kiểm thử', 'answer_correct_id' => 0, 'certification_test_id' => $course->id]);
        $answer = AnswerCertificationTest::create(['answer' => 'Đúng', 'question_certification_test_id' => $question->id]);
        $question->update(['answer_correct_id' => $answer->id]);
        return [$course, [$question->id => $answer->id]];
    }

    private function payload($course, $answers): array
    {
        return ['courseId' => $course->id, 'selectedAnswers' => $answers, 'registration' => ['fullname' => 'Người kiểm thử', 'email' => 'form-test@example.test', 'custom_department' => 'Trường A', 'custom_birthday' => '2000-06-05', 'custom_unconfigured' => 'must not persist']];
    }

    public function test_admin_can_create_preview_edit_and_delete_unused_forms(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->get(route('registration-forms.create'))->assertOk()->assertSee('registration-form-builder', false);
        $this->post(route('registration-forms.store'), ['name' => 'Form mới', 'fields_json' => json_encode($this->fields())])->assertRedirect(route('registration-forms.index'));
        $form = RegistrationForm::where('name', 'Form mới')->firstOrFail();
        $this->assertCount(4, $form->fields);
        $this->get(route('registration-forms.edit', $form))->assertOk()->assertSee('Form mới');
        $this->put(route('registration-forms.update', $form), ['name' => 'Form đã đổi tên', 'fields_json' => json_encode($this->fields())])->assertRedirect();
        $this->assertSame('Form đã đổi tên', $form->fresh()->name);
        $this->delete(route('registration-forms.destroy', $form))->assertSessionHas('success');
        $this->assertSoftDeleted($form);
    }

    public function test_guest_and_non_admin_cannot_change_forms(): void
    {
        $form = $this->form();
        $this->get(route('registration-forms.create'))->assertRedirect(route('login'));
        $this->post(route('registration-forms.store'), ['name' => 'Blocked'])->assertRedirect(route('login'));
        $this->actingAs(new User(['type' => 'MANAGER']));
        $this->put(route('registration-forms.update', $form), ['name' => 'Blocked'])->assertRedirect();
        $this->delete(route('registration-forms.destroy', $form))->assertRedirect();
        $this->assertNotSoftDeleted($form);
        $this->assertSame('Form dành cho giáo viên', $form->fresh()->name);
    }

    public function test_malformed_schema_missing_identity_duplicate_keys_and_unsafe_keys_are_rejected(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $bad = [];
        $bad[] = 'not-json';
        $bad[] = json_encode(array_slice($this->fields(), 1));
        $fields = $this->fields(); $fields[0]['required'] = false; $bad[] = json_encode($fields);
        $fields = $this->fields(); $fields[2]['key'] = 'fullname'; $bad[] = json_encode($fields);
        $fields = $this->fields(); $fields[2]['key'] = 'status_send_mail'; $bad[] = json_encode($fields);
        $fields = $this->fields(); $fields[2]['options'] = []; $bad[] = json_encode($fields);
        $fields = $this->fields(); $fields[1]['type'] = 'text'; $bad[] = json_encode($fields);
        $count = RegistrationForm::count();
        foreach ($bad as $json) $this->post(route('registration-forms.store'), ['name' => 'Invalid', 'fields_json' => $json])->assertSessionHasErrors();
        $this->assertSame($count, RegistrationForm::count());
    }

    public function test_course_can_choose_a_form_and_used_form_cannot_be_deleted(): void
    {
        $form = $this->form(); [$course] = $this->course(null);
        $this->actingAs(new User(['type' => 'ADMIN']));
        $template = CertificateTemplate::firstOrFail();
        $payload = ['course' => $course->course, 'passing_score' => 1, 'status' => 'ACTIVE', 'certificate_template_id' => $template->id, 'registration_form_id' => $form->id];
        $this->put(route('certification.update', $course), $payload)->assertRedirect();
        $this->assertEquals($form->id, $course->fresh()->registration_form_id);
        $this->get(route('certification.edit', $course))->assertOk()->assertSee('registration-form-picker', false)->assertSee($form->name);
        $this->delete(route('registration-forms.destroy', $form))->assertSessionHas('error');
        $this->assertNotSoftDeleted($form);
        $payload['registration_form_id'] = 99999999;
        $this->put(route('certification.update', $course), $payload)->assertSessionHasErrors('registration_form_id');
        $payload['registration_form_id'] = '';
        $this->put(route('certification.update', $course), $payload)->assertRedirect();
        $this->assertNull($course->fresh()->registration_form_id);
        $form->delete(); $payload['registration_form_id'] = $form->id;
        $this->post(route('certification.store'), $payload)->assertSessionHasErrors('registration_form_id');
    }

    public function test_client_receives_selected_schema_and_submission_stores_only_configured_values(): void
    {
        Mail::fake(); $form = $this->form(); [$course, $answers] = $this->course($form);
        $this->get('/certification/'.$course->slug)->assertOk()->assertSee('custom_department')->assertViewHas('course', fn ($course) => $course->registration_fields[2]['label'] === 'Đơn vị công tác');
        $response = $this->postJson('/api/certification/submit-form', $this->payload($course, $answers))->assertOk()->assertJsonPath('status', '201')->assertJsonPath('data.isPass', true);
        $person = PersonCertificationTest::findOrFail($response->json('data.id'));
        $this->assertSame('', $person->phone);
        $this->assertNull($person->province_id);
        $this->assertSame('Trường A', $person->registration_data['custom_department']['value']);
        $this->assertArrayNotHasKey('custom_unconfigured', $person->registration_data);
        $fields = $this->fields(); $fields[2]['label'] = 'Nhãn thay đổi'; $form->update(['fields' => $fields]);
        $this->assertSame('Đơn vị công tác', $person->fresh()->registration_data['custom_department']['label']);
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->get(route('certification.show', $course))->assertOk()->assertSee('Trường A')->assertSee('Đơn vị công tác');
        // Optional province/school must not break certificate rendering locally.
        $this->get('/certification/download/'.$person->id)->assertOk()->assertHeader('content-type', 'application/pdf');
        Mail::assertSent(\App\Mail\SendCertificationMail::class, 1);
    }

    public function test_required_custom_fields_email_date_options_and_answers_are_validated_before_saving(): void
    {
        [$course, $answers] = $this->course($this->form()); $base = $this->payload($course, $answers); $count = PersonCertificationTest::count();
        foreach (['custom_department' => '', 'email' => 'invalid', 'custom_birthday' => '2026-99-40'] as $key => $value) {
            $data = $base; $data['registration'][$key] = $value;
            $this->postJson('/api/certification/submit-form', $data)->assertJsonPath('status', '422');
        }
        $data = $base; $data['registration']['custom_department'] = 'Không có trong form';
        $this->postJson('/api/certification/submit-form', $data)->assertJsonPath('status', '422');
        $data = $base; $data['selectedAnswers'] = [array_key_first($answers) => 999999];
        $this->postJson('/api/certification/submit-form', $data)->assertJsonPath('status', '422');
        $this->assertSame($count, PersonCertificationTest::count());
    }

    public function test_courses_without_selected_form_still_use_default_fields(): void
    {
        [$course, $answers] = $this->course(null);
        $this->get('/certification/'.$course->slug)->assertOk()->assertSee('name_school')->assertSee('province');
        $this->postJson('/api/certification/submit-form', $this->payload($course, $answers))->assertJsonPath('status', '422');
        $province = \App\Models\ProvinceCertificationTest::firstOrFail();
        $payload = $this->payload($course, $answers);
        $payload['registration'] = ['fullname' => 'Người kiểm thử', 'email' => 'default-form@example.test', 'phone' => '0901234567', 'name_school' => 'Trường kiểm thử', 'province' => $province->id];
        $this->postJson('/api/certification/submit-form', $payload)->assertJsonPath('status', '201');
    }

    public function test_game_courses_can_also_use_selected_forms(): void
    {
        [$course, $answers] = $this->course($this->form());
        $course->forceFill(['type' => 'QUIZGAME'])->save();
        $response = $this->postJson('/api/certification/submit-form-game', $this->payload($course, $answers))->assertOk();
        $this->assertSame(201, $response->json('status'), $response->json('msg'));
        $person = PersonGameCertificationTest::findOrFail($response->json('data.id'));
        $this->assertSame('Trường A', $person->registration_data['custom_department']['value']);
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->get(route('certification.showGame', $course))->assertOk()->assertSee('Trường A');
    }
}
