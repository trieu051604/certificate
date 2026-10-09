<?php

namespace App\Imports;

use App\Models\AnswerCertificationTest;
use App\Models\CertificationTest;
use App\Models\QuestionCertificationTest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuestionImport implements ToCollection, WithMultipleSheets
{
    public function __construct(protected CertificationTest $certification) {}

    public function sheets(): array
    {
        return [0 => $this];
    }

    public function collection(Collection $collection): void
    {
        $header = array_map(fn ($value) => Str::slug((string) $value), array_slice($collection->first()?->toArray() ?? [], 0, 6));
        if ($header !== ['cau-hoi', 'a', 'b', 'c', 'd', 'dap-an-dung']) {
            throw ValidationException::withMessages(['file' => 'Dòng đầu phải có 6 cột: Câu hỏi, A, B, C, D, Đáp án đúng.']);
        }

        $questions = [];
        foreach ($collection->slice(1) as $index => $row) {
            $values = array_map(fn ($value) => trim((string) $value), $row->toArray());
            if (!array_filter($values, fn ($value) => $value !== '')) continue;
            $data = [
                'question' => $values[0] ?? '',
                'answers' => array_combine(['A', 'B', 'C', 'D'], array_pad(array_slice($values, 1, 4), 4, '')),
                'correct' => strtoupper($values[5] ?? ''),
            ];
            $validator = Validator::make($data, [
                'question' => ['required', 'string', 'max:255'],
                'answers.*' => ['nullable', 'string', 'max:255'],
                'correct' => ['required', 'in:A,B,C,D'],
            ], [
                'question.required' => 'Thiếu nội dung câu hỏi.',
                'question.max' => 'Câu hỏi vượt quá 255 ký tự.',
                'answers.*.max' => 'Đáp án vượt quá 255 ký tự.',
                'correct.required' => 'Thiếu đáp án đúng ở cột F.',
                'correct.in' => 'Cột F phải là A, B, C hoặc D.',
            ]);
            if ($validator->fails()) {
                throw ValidationException::withMessages(['file' => 'Dòng '.($index + 1).': '.$validator->errors()->first()]);
            }
            if (count(array_filter($data['answers'], fn ($answer) => $answer !== '')) < 2 || $data['answers'][$data['correct']] === '') {
                throw ValidationException::withMessages(['file' => 'Dòng '.($index + 1).': cần ít nhất 2 lựa chọn và lựa chọn đúng phải có nội dung.']);
            }
            $questions[] = $data;
        }
        if (!$questions) {
            throw ValidationException::withMessages(['file' => 'File chưa có câu hỏi. Vui lòng nhập câu hỏi từ dòng 2.']);
        }

        // Validate all rows first; replace questions together so a failed import keeps the old set.
        DB::transaction(function () use ($questions) {
            $course = CertificationTest::whereKey($this->certification->id)->lockForUpdate()->firstOrFail();
            if ($course->persons()->exists() || $course->person_game()->exists()) {
                throw ValidationException::withMessages(['file' => 'Khóa học đã có bài nộp. Vui lòng tạo bản sao khóa học để import bộ câu hỏi mới.']);
            }
            $course->questions()->delete();
            foreach ($questions as $data) {
                $question = QuestionCertificationTest::create(['question' => $data['question'], 'certification_test_id' => $course->id]);
                foreach ($data['answers'] as $letter => $text) {
                    if ($text === '') continue;
                    $answer = AnswerCertificationTest::create(['answer' => $text, 'question_certification_test_id' => $question->id]);
                    if ($letter === $data['correct']) $question->answer_correct_id = $answer->id;
                }
                $question->save();
            }
        });
    }
}