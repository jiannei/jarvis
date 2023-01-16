<?php

namespace App\Models\Github;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrendingMonthly extends Model
{
    use HasFactory;

    protected $table = 'github_trending_monthly';

    protected $fillable = [
        'repo',
        'desc',
        'language',
        'stars',
        'forks',
        'added_stars',
        'spoken_language_code',
        'month',
    ];
}
