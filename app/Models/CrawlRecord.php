<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CrawlRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
    ];
    protected $casts = [
        'content' => 'json',
    ];

    public function task()
    {
        return $this->belongsTo(CrawlTask::class, 'task_id');
    }
}
