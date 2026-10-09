<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsertLeadApi extends Model
{
    use HasFactory;
    protected $table = 'insert_lead_api';
    protected $guarded = [];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'insert_lead_id', 'id');
    }
}
