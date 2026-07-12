<?php

namespace App\Jobs;

use App\Models\Refund;
use App\Services\RefundAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRefundAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Refund $refund)
    {
    }

    public function handle(RefundAiService $service): void
    {
        $service->apply($this->refund);
    }
}
