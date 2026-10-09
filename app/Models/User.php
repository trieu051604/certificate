<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function is_admin()
    {
        return $this->type === 'ADMIN';
    }

    public function is_zns()
    {
        return $this->type === 'ZNS';
    }

    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'sale_id', 'id');
    }

    public function major_group()
    {
        // return $this->belongsTo(Major_Group::class,'major_group_id');
        return $this->belongsToMany(Major_Group::class, 'user_major_group', 'user_id', 'major_group_id');
    }

    public function log_point()
    {
        return $this->hasMany(LogPoint::class, 'user_id', 'id');
    }
}
