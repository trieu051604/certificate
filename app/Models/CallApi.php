<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallApi extends Model
{
    use HasFactory;
    protected $table = 'call_api';
    protected $guarded = [];
}
