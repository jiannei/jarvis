<?php

namespace App\Models\Gitee;

use App\Models\Model;

class TrendingDaily extends Model
{
    protected $table = 'gitee_trending_daily';

    protected $fillable = [
        'repo',
        'desc',
        'language',
        'stars',
        'day',
    ];
}
