<?php

namespace App\Jobs;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRefundNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Refund $refund,
        protected string $type,
        protected ?string $message = null
    ) {
    }

    public function handle(): void
    {
        if (! $this->refund->email) {
            return;
        }

        $subject = match ($this->type) {
            'submission' => 'Refund request received',
            'approved' => 'Refund update',
            'returned' => 'Refund update',
            'rejected' => 'Refund update',
            'paid' => 'Refund update',
            default => 'Refund update',
        };

        $body = match ($this->type) {
            'submission' => "Hello {$this->refund->first_name} {$this->refund->last_name},\n\nYour refund request {$this->refund->reference} has been received and is being reviewed.\n\nThank you.",
            'approved' => "Hello {$this->refund->first_name} {$this->refund->last_name},\n\nYour refund request {$this->refund->reference} has been approved.\n\nThank you.",
            'returned' => "Hello {$this->refund->first_name} {$this->refund->last_name},\n\nYour refund request {$this->refund->reference} has been returned for review.\n\nThank you.",
            'rejected' => "Hello {$this->refund->first_name} {$this->refund->last_name},\n\nYour refund request {$this->refund->reference} has been rejected.\n\nThank you.",
            'paid' => "Hello {$this->refund->first_name} {$this->refund->last_name},\n\nYour refund request {$this->refund->reference} has been paid.\n\nThank you.",
            default => "Hello {$this->refund->first_name} {$this->refund->last_name},\n\nYour refund request {$this->refund->reference} has been updated.\n\nThank you.",
        };

        Mail::raw($body, function ($message) use ($subject) {
            $message->to($this->refund->email)
                ->subject($subject);
        });
    }
}
