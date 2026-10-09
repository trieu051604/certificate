<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Vkovic\LaravelModelMeta\Models\Traits\HasMetadata;

class Setting extends Model
{
    use HasFactory;
    use Notifiable, HasMetadata;
    
    protected $fillable = [
        'name'
    ];
}
