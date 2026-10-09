<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major_Group extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $table = 'major_group';
    protected $guarded = [];
    public function users()
    {
        // return $this->hasMany(User::class, 'major_group_id', 'id');
        return $this->belongsToMany(User::class, 'user_major_group', 'major_group_id', 'user_id');
    }

    public function majors()
    {
        return $this->hasMany(Major::class, 'major_group_id', 'id');
    }

    public function share_lead()
    {
        return $this->hasMany(ShareLead::class, 'major_group_id', 'id');
    }
}
