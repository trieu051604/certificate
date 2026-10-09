<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareLead extends Model
{
    use HasFactory;
    protected $table = 'share_lead';
    protected $guarded = [];
    public function major_group()
    {
        return $this->belongsTo(Major_Group::class, 'major_group_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'share_lead_id', 'id');
    }
}
