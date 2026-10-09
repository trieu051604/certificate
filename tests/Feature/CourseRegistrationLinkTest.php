<?php

namespace Tests\Feature;

use App\Models\AnswerCertificationTest;
use App\Models\CertificateTemplate;
use App\Models\CertificationTest;
use App\Models\QuestionCertificationTest;
use App\Models\RegistrationForm;
use App\Models\User;
use App\Services\RegistrationFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CourseRegistrationLinkTest extends TestCase
{
    use DatabaseTransactions;

    private function payload(?RegistrationForm $form = null): array
    {
        return [
            'course' => 'Khóa học trùng tên kiểm thử form',
            'passing_score' => 1,
            'status' => 'ACTIVE',
            'certificate_template_id' => CertificateTemplate::firstOrFail()->id,
            'registration_form_id' => $form?->id,
        ];
    }

    private function createCourse(array $payload): CertificationTest
    {
        $this->post(route('certification.store'), $payload)->assertRedirect(route('certification.index'));
        return CertificationTest::where('course', $payload['course'])->latest('id')->firstOrFail();
    }

    private function addQuestion(CertificationTest $course): void
    {
        $question = QuestionCertificationTest::create([
            'question' => 'Câu hỏi kiểm thử', 'answer_correct_id' => 0, 'certification_test_id' => $course->id,
        ]);
        $answer = AnswerCertificationTest::create(['answer' => 'Đúng', 'question_certification_test_id' => $question->id]);
        $question->update(['answer_correct_id' => $answer->id]);
    }

    public function test_same_named_courses_open_their_own_saved_registration_form(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $form = RegistrationForm::create(['name' => 'Form Đoàn thanh niên kiểm thử', 'fields' => [
            ...array_slice(RegistrationFields::defaults(), 0, 2),
            ['key' => 'custom_department', 'label' => 'Đơn vị', 'type' => 'text', 'required' => true, 'placeholder' => '', 'options' => []],
        ]]);
        $old = $this->createCourse($this->payload());
        $new = $this->createCourse($this->payload($form));
        $this->assertNotSame($old->slug, $new->slug);
        $this->addQuestion($old);
        $this->addQuestion($new);

        $this->get(route('certification.exam', $old->slug))->assertOk()
            ->assertViewHas('course', fn ($course) => $course->id === $old->id && $course->registration_fields === RegistrationFields::defaults());
        $this->get(route('certification.exam', $new->slug))->assertOk()
            ->assertViewHas('course', fn ($course) => $course->id === $new->id && $course->registration_fields === $form->fields);

        // Saving the chosen form again must retain the course's unique, published URL.
        $this->put(route('certification.update', $new), $this->payload($form))->assertRedirect();
        $this->assertSame($new->slug, $new->fresh()->slug);
        $this->get(route('certification.exam', $new->slug))->assertOk()->assertSee('custom_department');
    }

    public function test_renaming_a_course_to_an_existing_name_does_not_reuse_its_link(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $old = $this->createCourse($this->payload());
        $new = $this->createCourse(array_replace($this->payload(), ['course' => 'Tên khác để kiểm thử đường dẫn']));
        $this->put(route('certification.update', $new), $this->payload())->assertRedirect();
        $this->assertNotSame($old->slug, $new->fresh()->slug);
    }

    public function test_admin_links_use_the_frontend_site_and_show_the_saved_form(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $form = RegistrationForm::create(['name' => 'Form kiểm thử đường dẫn local', 'fields' => RegistrationFields::defaults()]);
        $course = $this->createCourse($this->payload($form));
        config(['certificates.frontend_url' => 'https://frontend.example.test']);
        $url = route('certification.exam', $course->slug);
        $this->get(route('certification.index'))->assertOk()->assertSee($course->publicUrl())->assertSee($form->name)
            ->assertDontSee('https://certificate-uni.io.vn/');
        $this->get(route('certification.edit', $course))->assertOk()->assertSee($course->publicUrl())
            ->assertSee('Khóa học chưa có câu hỏi');
        $this->get($url)->assertOk()->assertViewIs('certification-unavailable')->assertSee('Khóa học chưa mở!');
    }

    public function test_a_course_name_without_slug_characters_still_gets_a_unique_link(): void
    {
        $this->actingAs(new User(['type' => 'ADMIN']));
        $first = $this->createCourse(array_replace($this->payload(), ['course' => '!!!']));
        $second = $this->createCourse(array_replace($this->payload(), ['course' => '!!!']));
        $this->assertSame('khoa-hoc', $first->slug);
        $this->assertSame('khoa-hoc-2', $second->slug);
    }
}
