<?php

namespace App\Models\Gitee;

use App\Models\Model;

class Explore extends Model
{
    protected $table = 'gitee_explore';

    protected $fillable = [
        'repo',
        'desc',
        'language',
        'category',
        'stars',
        'latest_updated_at',
    ];
}
