<?php

namespace App\Models\Gitee;

use App\Models\Model;

class TrendingWeekly extends Model
{
    protected $table = 'gitee_trending_weekly';

    protected $fillable = [
        'repo',
        'desc',
        'language',
        'stars',
        'week',
    ];
}
