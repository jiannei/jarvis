<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ActivityLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private string $description,
        private ?User $causer = null,
        private array $properties = [],
        private string $name = 'default'
    ) {
        //
        $this->onQueue('log');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        activity($this->name)
            ->causedBy($this->causer)
            ->withProperties($this->properties)
            ->log($this->description);
    }
}
