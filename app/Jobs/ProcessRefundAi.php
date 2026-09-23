<?php

namespace App\Jobs;

use App\Models\Refund;
use App\Services\RefundAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessRefundAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Maximum retry attempts.
     */
    public int $tries = 5;


    /**
     * Retry delay.
     */
    public int $backoff = 120;



    public function __construct(
        protected Refund $refund
    ) {
        // Send AI tasks to a dedicated queue.
        $this->onQueue('ai-processing');
    }



    public function handle(
        RefundAiService $service
    ): void {

        $service->apply($this->refund);

    }



    /**
     * Runs when all retries fail.
     */
    public function failed(
        Throwable $exception
    ): void {

        Log::error(
            'Refund AI processing failed',
            [
                'refund_id' => $this->refund->id,
                'reference' => $this->refund->reference,
                'error' => $exception->getMessage(),
            ]
        );


        $this->refund->update([
            'ai_flagged' => true,
        ]);
    }
}