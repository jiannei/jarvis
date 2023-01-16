<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable = [
        'title',
        'author',
        'content',
        'channel',
        'link',
        'category',
        'published_at',
    ];
}
