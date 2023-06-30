<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadRunnerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_id',
        'status',
        'success',
        'message'
    ];
}
