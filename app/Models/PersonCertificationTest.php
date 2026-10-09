<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonCertificationTest extends Model
{
    use HasFactory;
    protected $table = 'person_certification_test';
    protected $fillable = [
        'fullname', 'phone', 'email', 'point', 'answer_json', 'school_id', 'province_id', 'certification_test_id', 'image_cer', 'msg_send_mail', 'status_send_mail','name_school','you_is', 'registration_data'
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
