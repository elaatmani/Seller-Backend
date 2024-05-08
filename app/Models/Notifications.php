<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','title','message','status','type','source','priority','action','read_at','options'
    ];

    protected $casts = [
        'options' => 'json'
    ];
}
