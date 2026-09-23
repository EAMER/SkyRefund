<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Department;
use App\Enums\Priority;
use App\Enums\RefundStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Return dashboard statistics for admin UI cards, scoped identically to
     * RefundController::index() — same tenant isolation, same department
     * visibility — so these numbers always match what "Recent activity"
     * and the Refund List actually show. Previously this ran unscoped
     * Refund::count() queries across every airline and every department,
     * so a Commercial user could see "Pending Commercial: 1" while their
     * own (correctly scoped) refund list showed nothing — that mismatched
     * refund could have belonged to a different airline entirely.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $base = Refund::query();

        if (! $user->isSuperAdmin()) {
            $base->where('airline_id', $user->airline_id);
        }

        if (
            ! $user->isSuperAdmin()
            && ! $user->hasRole(UserRole::REFUND_OFFICER)
        ) {

            $departmentForRole = match (true) {
                $user->hasRole(UserRole::COMMERCIAL) => Department::COMMERCIAL,
                $user->hasRole(UserRole::AUDIT) => Department::AUDIT,
                $user->hasRole(UserRole::FINANCE) => Department::FINANCE,
                $user->hasRole(UserRole::TREASURY) => Department::TREASURY,
                default => null,
            };

            if ($departmentForRole) {
                $base->where('current_department', $departmentForRole);
            } else {
                $base->whereRaw('1 = 0');
            }
        }

        $stats = [
            'total_refunds' => (clone $base)->count(),
            'new_requests' => (clone $base)->where('current_status', RefundStatus::NEW_REQUEST->value)->count(),
            'pending_commercial' => (clone $base)->where('current_status', RefundStatus::PENDING_COMMERCIAL->value)->count(),
            'pending_audit' => (clone $base)->where('current_status', RefundStatus::PENDING_AUDIT->value)->count(),
            'pending_finance' => (clone $base)->where('current_status', RefundStatus::PENDING_FINANCE->value)->count(),
            'pending_treasury' => (clone $base)->where('current_status', RefundStatus::PENDING_TREASURY->value)->count(),
            'completed' => (clone $base)->where('current_status', RefundStatus::REFUND_COMPLETED->value)->count(),
            'rejected' => (clone $base)->where('current_status', RefundStatus::REJECTED->value)->count(),
            'cancelled' => (clone $base)->where('current_status', RefundStatus::CANCELLED->value)->count(),
            'high_priority' => (clone $base)->where('priority', Priority::HIGH->value)->count(),
            'medium_priority' => (clone $base)->where('priority', Priority::MEDIUM->value)->count(),
            'low_priority' => (clone $base)->where('priority', Priority::LOW->value)->count(),
            'flagged_by_ai' => (clone $base)->where('ai_flagged', true)->count(),
        ];

        return response()->json($stats);
    }
}