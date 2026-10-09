<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];
    public function major_group()
    {
        return $this->belongsTo(Major_Group::class,'major_group_id');
    }
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
