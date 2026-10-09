<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $guarded = [];
    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }
    public function import()
    {
        return $this->belongsTo(Import::class, 'import_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'sale_id');
    }

    public function share_lead()
    {
        return $this->belongsTo(ShareLead::class, 'share_lead_id');
    }

    public function insert_lead_api()
    {
        return $this->belongsTo(InsertLeadApi::class, 'insert_lead_id');
    }

    public function log_convert()
    {
        return $this->belongsTo(LogPoint::class, 'convert_id');
    }
}
