<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'sheet_id',
        'sheet_name',
        'auto_fetch',
        'active'
    ];
    protected $appends = ['username'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function getUsernameAttribute()
    {
        return $this->user->username;
    }
}
