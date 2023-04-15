<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStateVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_state_id',
        'size',
        'color',
        'quantity'
    ];

    public function inventoryState(){
        return $this->belongsTo(InventoryState::class,'inventory_state_id');
    }
}
