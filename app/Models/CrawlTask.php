<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CrawlTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'expression',
        'pattern',
        'active',
    ];

    protected $casts = [
        'pattern' => 'json'
    ];

    public function records()
    {
        return $this->hasMany(CrawlRecord::class,'task_id');
    }
}
