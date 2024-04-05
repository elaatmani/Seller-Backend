<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_at',
        'content',
        'type',
        'is_active',
        'variant',
        'target',
        'closeable',
        'direction'
    ];

    protected $appends = [ 'to' ];

    public function getToAttribute() {
        switch ($this->type) {
            case 'user':
                $user = User::where('id', $this->target)->first();
                return $user->firstname . ' ' . $user->lastname;
            break;
            default:
                return ucfirst($this->target);
            break;
        }
    }
}
