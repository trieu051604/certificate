<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPoint extends Model
{
    use HasFactory;
    protected $table = 'log_points';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'convert_id', 'id');
    }
}
