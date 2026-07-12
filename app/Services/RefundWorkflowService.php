<?php

namespace App\Services;

use App\Enums\Department;
use App\Enums\RefundStatus;
use App\Models\Refund;
use App\Services\RefundNotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RefundWorkflowService
{
    public function __construct(
        protected RefundNotificationService $notificationService
    ) {
    }

    /**
     * Internal helper used by all workflow actions.
     */
    protected function changeStatus(
        Refund $refund,
        RefundStatus $newStatus,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {

        $currentStatus = RefundStatus::from($refund->current_status);

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate updates
        |--------------------------------------------------------------------------
        */

        if ($currentStatus === $newStatus) {
            throw new InvalidArgumentException(
                "Refund is already {$newStatus->label()}."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate workflow transition
        |--------------------------------------------------------------------------
        */

        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Invalid workflow transition from {$currentStatus->label()} to {$newStatus->label()}."
            );
        }

        DB::transaction(function () use (
            $refund,
            $currentStatus,
            $newStatus,
            $note,
            $changedBy
        ) {
            $department = match ($newStatus) {
                RefundStatus::NEW_REQUEST => Department::REFUND,
                RefundStatus::PENDING_COMMERCIAL, RefundStatus::RETURNED_BY_COMMERCIAL => Department::COMMERCIAL,
                RefundStatus::PENDING_AUDIT, RefundStatus::RETURNED_BY_AUDIT => Department::AUDIT,
                RefundStatus::PENDING_FINANCE, RefundStatus::RETURNED_BY_FINANCE => Department::FINANCE,
                RefundStatus::PENDING_TREASURY => Department::TREASURY,
                RefundStatus::REFUND_COMPLETED, RefundStatus::REJECTED, RefundStatus::CANCELLED => Department::TREASURY,
            };

            $refund->update([
                'current_status' => $newStatus->value,
                'current_department' => $department->value,
            ]);

            $refund->statusLogs()->create([
                'changed_by' => $changedBy,
                'old_status' => $currentStatus->value,
                'new_status' => $newStatus->value,
                'note' => $note,
            ]);

            $event = match ($newStatus) {
                RefundStatus::PENDING_COMMERCIAL => 'approved',
                RefundStatus::PENDING_AUDIT => 'approved',
                RefundStatus::PENDING_FINANCE => 'approved',
                RefundStatus::PENDING_TREASURY => 'approved',
                RefundStatus::REFUND_COMPLETED => 'paid',
                RefundStatus::REJECTED => 'rejected',
                RefundStatus::RETURNED_BY_COMMERCIAL, RefundStatus::RETURNED_BY_AUDIT, RefundStatus::RETURNED_BY_FINANCE => 'returned',
                default => null,
            };

            if ($event) {
                $this->notificationService->sendStatusNotification($refund, $event);
            }
        });

        return $refund->fresh([
            'airline',
            'tickets',
            'attachments',
            'statusLogs',
        ]);
    }

    /**
     * Approve and move to the next workflow stage.
     */
    public function approve(
        Refund $refund,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {

        $currentStatus = RefundStatus::from($refund->current_status);

        $nextStatus = $currentStatus->next();

        if (! $nextStatus) {
            throw new InvalidArgumentException(
                'This refund cannot be approved.'
            );
        }

        return $this->changeStatus(
            refund: $refund,
            newStatus: $nextStatus,
            note: $note,
            changedBy: $changedBy,
        );
    }

    /**
     * Return the refund to the previous workflow stage.
     */
    public function returnBack(
        Refund $refund,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {

        $currentStatus = RefundStatus::from($refund->current_status);

        $previousStatus = $currentStatus->returnedTo();

        if (! $previousStatus) {
            throw new InvalidArgumentException(
                'This refund cannot be returned.'
            );
        }

        return $this->changeStatus(
            refund: $refund,
            newStatus: $previousStatus,
            note: $note,
            changedBy: $changedBy,
        );
    }

    /**
     * Reject the refund.
     */
    public function reject(
        Refund $refund,
        string $reason,
        ?int $changedBy = null
    ): Refund {

        return $this->changeStatus(
            refund: $refund,
            newStatus: RefundStatus::REJECTED,
            note: $reason,
            changedBy: $changedBy,
        );
    }

    /**
     * Cancel the refund.
     */
    public function cancel(
        Refund $refund,
        string $reason,
        ?int $changedBy = null
    ): Refund {

        return $this->changeStatus(
            refund: $refund,
            newStatus: RefundStatus::CANCELLED,
            note: $reason,
            changedBy: $changedBy,
        );
    }

    /**
     * Complete the refund.
     */
    public function complete(
        Refund $refund,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {

        return $this->changeStatus(
            refund: $refund,
            newStatus: RefundStatus::REFUND_COMPLETED,
            note: $note,
            changedBy: $changedBy,
        );
    }
}