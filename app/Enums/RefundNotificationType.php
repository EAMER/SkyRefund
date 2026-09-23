<?php

namespace App\Enums;

enum RefundNotificationType: string
{
    case SUBMISSION = 'submission';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';
    case RETURNED = 'returned';
}