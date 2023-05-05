<?php

namespace App\Console\Commands;

use App\Models\CrawlTask;
use Cron\CronExpression;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class Crawl extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬取任务';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        $tasks = CrawlTask::query()->where('active', true)->where('next_run_date', '<=', Carbon::now())->get();

        $tasks->each(function ($task) {
            $this->comment('正在调度：'.$task->name);

            dispatch(function () use ($task) {
                DB::transaction(function () use ($task){
                    $result = [];

                    try {
                        $result = Crawler::pattern($task->pattern);
                        $task->previous_run_date = Carbon::now();
                        $task->next_run_date = Carbon::instance((new CronExpression($task->expression))->getNextRunDate());
                        $task->status = 1;
                        $task->exception = '';
                    } catch (\Throwable $exception) {
                        $task->status = -1;
                        $task->exception = $exception;
                    }

                    $task->save();

                    if (is_array($result)) {
                        $result = collect($result);
                    }

                    $contents = $result->map(function ($item) {
                        return ['content' => $item];
                    });

                    $task->records()->createMany($contents->all());
                });
            });
        });

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
