<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrationForm extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'fields'];
    protected $casts = ['fields' => 'array'];

    public function courses()
    {
        return $this->hasMany(CertificationTest::class);
    }
}
