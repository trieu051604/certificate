<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvinceCertificationTest extends Model
{
    use HasFactory;
    protected $table = 'province_certification';
    protected $fillable = ['province'];
}
