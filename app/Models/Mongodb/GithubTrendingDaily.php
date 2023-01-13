<?php

namespace App\Models\Mongodb;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class GithubTrendingDaily extends Model
{
    use HasFactory;

    protected $collection = 'github_trending_daily';
}
