<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Priority;
use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Return dashboard statistics for admin UI cards.
     */
    public function index(): JsonResponse
    {
        $stats = [
            'total_refunds' => Refund::count(),
            'new_requests' => Refund::where('current_status', RefundStatus::NEW_REQUEST->value)->count(),
            'pending_commercial' => Refund::where('current_status', RefundStatus::PENDING_COMMERCIAL->value)->count(),
            'pending_audit' => Refund::where('current_status', RefundStatus::PENDING_AUDIT->value)->count(),
            'pending_finance' => Refund::where('current_status', RefundStatus::PENDING_FINANCE->value)->count(),
            'pending_treasury' => Refund::where('current_status', RefundStatus::PENDING_TREASURY->value)->count(),
            'completed' => Refund::where('current_status', RefundStatus::REFUND_COMPLETED->value)->count(),
            'rejected' => Refund::where('current_status', RefundStatus::REJECTED->value)->count(),
            'cancelled' => Refund::where('current_status', RefundStatus::CANCELLED->value)->count(),
            'high_priority' => Refund::where('priority', Priority::HIGH->value)->count(),
            'medium_priority' => Refund::where('priority', Priority::MEDIUM->value)->count(),
            'low_priority' => Refund::where('priority', Priority::LOW->value)->count(),
            'flagged_by_ai' => Refund::where('ai_flagged', true)->count(),
        ];

        return response()->json($stats);
    }
}