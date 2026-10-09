<?php

namespace Database\Seeders;

use App\Models\AnswerCertificationTest;
use App\Models\CertificationTest;
use App\Models\QuestionCertificationTest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('json/QA.json');
        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);
        
        try {
            \DB::beginTransaction();

            $course = CertificationTest::create([
                'course' => $data['course'],
                'passing_score' => $data['passing_score'],
                'slug' => \Str::slug($data['course']),
                'status' => 'ACTIVE'
            ]);

            foreach ($data['listQuestions'] as $question) {
                $questionInsert = QuestionCertificationTest::create([
                    'question' => $question['question'],
                    'certification_test_id' => $course->id
                ]);

                foreach ($question['answers'] as $answer) {
                    $answerInsert = AnswerCertificationTest::create([
                        'answer' => $answer['answer'],
                        'question_certification_test_id' => $questionInsert->id
                    ]);

                    if ($answer['id'] == $question['answer_correct']) {
                        $questionInsert->answer_correct_id = $answerInsert->id;
                        $questionInsert->save();
                    }
                }
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            dd($th->getMessage());
        }
    }
}
