<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonGameCertificationTest extends Model
{
    use HasFactory;
    protected $table = 'person_game_certification_test';
    protected $fillable = [
        'fullname', 'phone', 'email', 'point', 'answer_json', 'school_id', 'province_id', 'major', 'certification_test_id', 'image_cer', 'name_school', 'you_is', 'registration_data'
    ];
    protected $casts = ['registration_data' => 'array'];

    public function certification()
    {
        return $this->belongsTo(CertificationTest::class, 'certification_test_id');
    }

    public function isPass()
    {
        if ($this->certification) {
            return $this->point >= $this->certification->passing_score;
        }
        return false;
    }

    public function school()
{
    return $this->hasOne(SchoolCertificationTest::class, 'id', 'school_id');
}

    public function province()
    {
        return $this->hasOne(ProvinceCertificationTest::class, 'id', 'province_id');
    }
}
