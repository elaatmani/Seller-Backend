<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TemporaryMedia extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'temporary_media';

    protected $fillable = [
        'uuid',
        'collection_name'
    ];
}
