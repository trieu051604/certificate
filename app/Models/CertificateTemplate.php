<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'background', 'width', 'height', 'layout'];

    public function courses()
    {
        return $this->hasMany(CertificationTest::class);
    }
}
