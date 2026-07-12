<?php

namespace App\Services;

use App\Jobs\SendRefundNotification;
use App\Models\Refund;
use Illuminate\Support\Facades\Bus;

class RefundNotificationService
{
    public function sendSubmissionNotification(Refund $refund): void
    {
        Bus::dispatch(new SendRefundNotification($refund, 'submission'));
    }

    public function sendStatusNotification(Refund $refund, string $event): void
    {
        Bus::dispatch(new SendRefundNotification($refund, $event));
    }
}
