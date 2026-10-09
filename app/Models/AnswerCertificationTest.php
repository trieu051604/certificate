<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerCertificationTest extends Model
{
    use HasFactory;
    protected $table = 'answer_certification_test';
    protected $fillable = [
        'answer', 'question_certification_test_id'
    ];

    public function question()
    {
        return $this->belongsTo(QuestionCertificationTest::class, 'question_certification_test_id');
    }
}
