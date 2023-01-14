<?php

namespace App\Models\Github;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrendingDaily extends Model
{
    use HasFactory;

    protected $table = 'github_trending_daily';

    protected $fillable = [
        'repo',
        'desc',
        'language',
        'stars',
        'forks',
        'added_stars',
        'spoken_language_code',
        'day',
    ];
}
