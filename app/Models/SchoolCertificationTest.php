<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolCertificationTest extends Model
{
    use HasFactory;
    protected $table = 'school_certification';
    protected $fillable = ['school', 'province_id'];
}
