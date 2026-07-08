<?php

namespace App\Services;

use App\Enums\RefundStatus;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RefundWorkflowService
{
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

            $refund->update([
                'current_status' => $newStatus->value,
            ]);

            $refund->statusLogs()->create([
                'changed_by' => $changedBy,
                'old_status' => $currentStatus->value,
                'new_status' => $newStatus->value,
                'note' => $note,
            ]);
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