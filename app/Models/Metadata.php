<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type', 'model_id', 'meta_key', 'meta_value',
    ];

    // Define polymorphic relationship
    public function model()
    {
        return $this->morphTo();
    }
}
