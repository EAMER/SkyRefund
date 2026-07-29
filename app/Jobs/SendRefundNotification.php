<?php

namespace App\Jobs;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendRefundNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of retry attempts.
     */
    public int $tries = 5;

    /**
     * Retry delay in seconds.
     */
    public int $backoff = 60;


    public function __construct(
        protected Refund $refund,
        protected string $type,
        protected ?string $message = null
    ) {
    }


    public function handle(): void
    {
        if (! $this->refund->email) {
            Log::warning(
                'Refund notification skipped: no email address',
                [
                    'refund_id' => $this->refund->id
                ]
            );

            return;
        }


        $notification = $this->buildNotification();


        Mail::raw(
            $notification['body'],
            function ($mail) use ($notification) {

                $mail->to($this->refund->email)
                    ->subject($notification['subject']);
            }
        );
    }


    private function buildNotification(): array
    {
        $name = "{$this->refund->first_name} {$this->refund->last_name}";
        $reference = $this->refund->reference;


        return match ($this->type) {

            'submission' => [
                'subject' => 'Refund request received',
                'body' =>
                    "Hello {$name},\n\n" .
                    "Your refund request {$reference} has been received and is currently being reviewed.\n\n" .
                    "Thank you."
            ],


            'approved' => [
                'subject' => 'Refund request approved',
                'body' =>
                    "Hello {$name},\n\n" .
                    "Your refund request {$reference} has been approved.\n\n" .
                    "Thank you."
            ],


            'rejected' => [
                'subject' => 'Refund request rejected',
                'body' =>
                    "Hello {$name},\n\n" .
                    "Your refund request {$reference} has been rejected.\n\n" .
                    "Thank you."
            ],


            'returned' => [
                'subject' => 'Refund request returned',
                'body' =>
                    "Hello {$name},\n\n" .
                    "Your refund request {$reference} has been returned for additional review.\n\n" .
                    "Thank you."
            ],


            'paid' => [
                'subject' => 'Refund payment completed',
                'body' =>
                    "Hello {$name},\n\n" .
                    "Your refund request {$reference} has been paid successfully.\n\n" .
                    "Thank you."
            ],


            default => [
                'subject' => 'Refund status update',
                'body' =>
                    "Hello {$name},\n\n" .
                    "Your refund request {$reference} has been updated.\n\n" .
                    "Thank you."
            ],
        };
    }


    /**
     * Called after all retries fail.
     */
    public function failed(Throwable $exception): void
    {
        Log::error(
            'Refund notification failed permanently',
            [
                'refund_id' => $this->refund->id,
                'notification_type' => $this->type,
                'error' => $exception->getMessage()
            ]
        );
    }
}