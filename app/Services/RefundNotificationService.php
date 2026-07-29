<?php

namespace App\Services;

use App\Jobs\SendRefundNotification;
use App\Models\Refund;

class RefundNotificationService
{
    public function sendSubmissionNotification(Refund $refund): void
    {
        SendRefundNotification::dispatch(
            $refund,
            'submission'
        );
    }


    public function sendStatusNotification(
        Refund $refund,
        string $event
    ): void {
        SendRefundNotification::dispatch(
            $refund,
            $event
        );
    }
}