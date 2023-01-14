<?php

namespace App\Models\Github;

use App\Models\Model;

class TrendingSpokenLanguage extends Model
{
    protected $table = 'github_trending_spoken_languages';

    protected $fillable = [
        'name',
        'code',
    ];
}
