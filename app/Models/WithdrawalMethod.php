<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'type',
        'metadata',
        'is_preferred'
    ];

    protected $casts = [
        'metadata' =>'json'
    ];

    public function seller() {
        return $this->belongsTo(User::class, 'id', 'seller_id');
    }
}
