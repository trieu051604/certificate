<?php

namespace Tests\Feature;

use App\Imports\QuestionImport;
use App\Models\AnswerCertificationTest;
use App\Models\CertificateTemplate;
use App\Models\CertificationTest;
use App\Models\PersonCertificationTest;
use App\Models\QuestionCertificationTest;
use App\Models\RegistrationForm;
use App\Models\User;
use App\Services\RegistrationFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class QuestionImportTest extends TestCase
{
    use DatabaseTransactions;

    private function course(): CertificationTest
    {
        $form = RegistrationForm::create(['name' => 'Form import kiểm thử', 'fields' => RegistrationFields::defaults()]);
        return CertificationTest::create([
            'course' => 'Khóa câu hỏi kiểm thử', 'slug' => 'question-import-test', 'passing_score' => 1, 'status' => 'ACTIVE',
            'certificate_template_id' => CertificateTemplate::firstOrFail()->id, 'registration_form_id' => $form->id,
            'subject' => 'Mail [course]', 'content' => '<p>Đơn vị [unit]</p>',
        ]);
    }

    private function rows(array $questions): Collection
    {
        return collect([['Câu hỏi', 'A', 'B', 'C', 'D', 'Đáp án đúng'], ...$questions])->map(fn ($row) => collect($row));
    }

    private function submitted(CertificationTest $course): void
    {
        PersonCertificationTest::create(['fullname' => 'Người kiểm thử', 'email' => 'question-test@example.test', 'phone' => '',
            'point' => 0, 'answer_json' => '{}', 'certification_test_id' => $course->id]);
    }

    public function test_valid_questions_replace_the_old_set_and_client_receives_new_content(): void
    {
        $course = $this->course();
        $old = QuestionCertificationTest::create(['question' => 'Câu cũ', 'certification_test_id' => $course->id]);
        $oldAnswer = AnswerCertificationTest::create(['answer' => 'Đáp án cũ', 'question_certification_test_id' => $old->id]);
        (new QuestionImport($course))->collection($this->rows([
            ['AI có vai trò gì?', 'Hỗ trợ giáo viên', 'Thay thế toàn bộ', '', '', ' a '],
            ['', '', '', '', '', ''],
            ['Số nào bằng không?', 0, 1, 2, 3, 'A'],
        ]));
        $this->assertModelMissing($old);
        $this->assertModelMissing($oldAnswer);
        $this->assertSame(2, $course->questions()->count());
        $first = $course->questions()->with('answers')->first();
        $this->assertSame('Hỗ trợ giáo viên', $first->answers->firstWhere('id', $first->answer_correct_id)->answer);
        $this->get(route('certification.exam', $course->slug))->assertOk()
            ->assertViewHas('course', fn ($value) => $value->questions->count() === 2 && $value->questions->first()->question === 'AI có vai trò gì?');
    }

    public function test_an_invalid_later_row_keeps_every_old_question_and_answer(): void
    {
        $course = $this->course();
        $old = QuestionCertificationTest::create(['question' => 'Giữ lại câu này', 'certification_test_id' => $course->id]);
        $answer = AnswerCertificationTest::create(['answer' => 'Giữ đáp án', 'question_certification_test_id' => $old->id]);
        try {
            (new QuestionImport($course))->collection($this->rows([['Hợp lệ', 'A1', 'B1', '', '', 'A'], ['Lỗi đáp án đúng', 'A2', 'B2', '', '', 'D']]));
            $this->fail('Invalid row accepted');
        } catch (ValidationException $error) {
            $this->assertStringContainsString('Dòng 3', $error->errors()['file'][0]);
        }
        $this->assertModelExists($old);
        $this->assertModelExists($answer);
        $this->assertSame(1, $course->questions()->count());
    }

    public function test_a_submitted_course_explains_the_import_block_and_does_not_replace_questions(): void
    {
        $course = $this->course(); $this->submitted($course);
        $old = QuestionCertificationTest::create(['question' => 'Câu đã thi', 'certification_test_id' => $course->id]);
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->post(route('admin.certification.importStore'), ['certification_id' => $course->id])
            ->assertRedirect(route('admin.certification.import', $course))->assertSessionHas('level', 'warning');
        $this->get(route('admin.certification.import', $course))->assertOk()->assertSee('đã có 1 bài nộp')
            ->assertSee('Tạo bản sao để import câu hỏi mới')->assertSee('Câu đã thi');
        $this->assertModelExists($old);
    }

    public function test_course_copy_prefills_settings_and_has_a_separate_link_and_question_set(): void
    {
        $course = $this->course(); $this->submitted($course);
        $this->actingAs(new User(['type' => 'ADMIN']));
        $this->get(route('certification.create', ['copy_from' => $course->id]))->assertOk()
            ->assertSee('Tạo bản sao khóa #'.$course->id)->assertSee('Mail [course]')->assertSee('Đơn vị [unit]')
            ->assertSee('value="'.$course->certificate_template_id.'" data-image=', false)
            ->assertSee('initial="'.$course->registration_form_id.'"', false);
        $payload = $course->only(['course', 'passing_score', 'status', 'certificate_template_id', 'registration_form_id', 'subject', 'content']);
        $response = $this->post(route('certification.store'), $payload + ['copy_from' => $course->id]);
        $copy = CertificationTest::latest('id')->firstOrFail();
        $response->assertRedirect(route('admin.certification.import', $copy));
        $this->assertNotSame($course->id, $copy->id);
        $this->assertNotSame($course->slug, $copy->slug);
        $this->assertSame($course->registration_form_id, $copy->registration_form_id);
        $this->assertSame(0, $copy->questions()->count());
        $this->assertSame(0, $copy->persons()->count());
        $this->assertSame(1, $course->persons()->count());
    }

    public function test_the_supplied_ai_spreadsheet_imports_twenty_questions_without_changing_other_courses(): void
    {
        $path = 'C:/Users/DELL/Downloads/import-question-demo-Quiz AI.xlsx';
        if (!is_file($path)) $this->markTestSkipped('Local user spreadsheet is not present.');
        $course = $this->course();
        Excel::import(new QuestionImport($course), $path);
        $this->assertSame(20, $course->questions()->count());
        $this->assertSame('AI nên được sử dụng trong dạy học với vai trò nào?', $course->questions()->first()->question);
        foreach ($course->questions()->with('answers')->get() as $question) {
            $this->assertCount(4, $question->answers);
            $this->assertNotNull($question->answers->firstWhere('id', $question->answer_correct_id));
        }
    }
}
