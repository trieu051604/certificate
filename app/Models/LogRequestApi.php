<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogRequestApi extends Model
{
    use HasFactory;
    protected $table = 'log_request_api';
    protected $guarded = [];
}
