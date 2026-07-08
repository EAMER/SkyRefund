<?php

namespace App\Services;

use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RefundWorkflowService
{
    /**
     * Change the refund status.
     */
    public function changeStatus(
        Refund $refund,
        string $newStatus,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {

        DB::transaction(function () use (
            $refund,
            $newStatus,
            $note,
            $changedBy
        ) {

            $oldStatus = $refund->current_status;

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate updates
            |--------------------------------------------------------------------------
            */

            if ($oldStatus === $newStatus) {
                throw new InvalidArgumentException(
                    "Refund is already {$newStatus}."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update refund
            |--------------------------------------------------------------------------
            */

            $refund->update([
                'current_status' => $newStatus,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create audit log
            |--------------------------------------------------------------------------
            */

            $refund->statusLogs()->create([
                'changed_by' => $changedBy,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => $note,
            ]);
        });

        return $refund->fresh([
            'statusLogs',
        ]);
    }
}