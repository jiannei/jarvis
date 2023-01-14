<?php

namespace App\Models\Github;

use App\Models\Model;

class TrendingLanguage extends Model
{
    protected $table = 'github_trending_languages';

    protected $fillable = [
        'name',
        'code',
    ];
}
