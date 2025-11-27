<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'status',
        'finished_at',
        'user_id',
        'file'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files');
    }
}
