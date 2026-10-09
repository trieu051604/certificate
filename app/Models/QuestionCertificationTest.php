<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionCertificationTest extends Model
{
    use HasFactory;
    protected $table = 'question_certification_test';
    protected $fillable = [
        'question', 'answer_correct_id', 'certification_test_id'
    ];

    public function course() 
    {
        return $this->belongsTo(CertificationTest::class, 'certification_test_id');
    }

    public function answers()
    {
        return $this->hasMany(AnswerCertificationTest::class);
    }
}
