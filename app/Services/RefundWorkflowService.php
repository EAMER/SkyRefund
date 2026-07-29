<?php

namespace App\Services;

use App\Enums\Department;
use App\Enums\RefundStatus;
use App\Models\Refund;
use App\Models\RefundTicket;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RefundWorkflowService
{
    public function __construct(
        protected RefundNotificationService $notificationService
    ) {
    }


    /**
     * Change refund status internally.
     */
    protected function changeStatus(
        Refund $refund,
        RefundStatus $newStatus,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {

        $currentStatus = $refund->current_status instanceof RefundStatus
            ? $refund->current_status
            : RefundStatus::from($refund->current_status);


        if ($currentStatus === $newStatus) {

            throw new InvalidArgumentException(
                "Refund is already {$newStatus->label()}."
            );
        }


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

                RefundStatus::NEW_REQUEST =>
                    Department::REFUND,

                RefundStatus::PENDING_COMMERCIAL,
                RefundStatus::RETURNED_BY_COMMERCIAL =>
                    Department::COMMERCIAL,

                RefundStatus::PENDING_AUDIT,
                RefundStatus::RETURNED_BY_AUDIT =>
                    Department::AUDIT,

                RefundStatus::PENDING_FINANCE,
                RefundStatus::RETURNED_BY_FINANCE =>
                    Department::FINANCE,

                RefundStatus::PENDING_TREASURY =>
                    Department::TREASURY,

                RefundStatus::REFUND_COMPLETED,
                RefundStatus::REJECTED,
                RefundStatus::CANCELLED =>
                    Department::TREASURY,
            };


            $refund->update([

                'current_status' => $newStatus,

                'current_department' => $department,

            ]);



            $refund->statusLogs()->create([

                'changed_by' => $changedBy,

                'old_status' => $currentStatus->value,

                'new_status' => $newStatus->value,

                'note' => $note,

            ]);



            $event = match ($newStatus) {


                RefundStatus::PENDING_COMMERCIAL,
                RefundStatus::PENDING_AUDIT,
                RefundStatus::PENDING_FINANCE,
                RefundStatus::PENDING_TREASURY =>
                    'approved',


                RefundStatus::REFUND_COMPLETED =>
                    'paid',


                RefundStatus::REJECTED =>
                    'rejected',


                RefundStatus::RETURNED_BY_COMMERCIAL,
                RefundStatus::RETURNED_BY_AUDIT,
                RefundStatus::RETURNED_BY_FINANCE =>
                    'returned',


                default => null,
            };


            if ($event) {

                DB::afterCommit(function () use (
                    $refund,
                    $event
                ) {

                    $this->notificationService
                        ->sendStatusNotification(
                            $refund,
                            $event
                        );

                });

            }


        });


        return $refund->fresh([
            'airline',
            'tickets',
            'attachments',
            'statusLogs',
        ]);

    }






    public function adjustTicketAmount(
    RefundTicket $ticket,
    float $newAmount,
    string $reason,
    ?int $changedBy = null
): RefundTicket {

    if ($newAmount > (float) $ticket->fare_paid) {
        throw new InvalidArgumentException(
            'Refund amount cannot exceed the fare paid.'
        );
    }

    DB::transaction(function () use ($ticket, $newAmount, $reason, $changedBy) {

        $ticket->amountLogs()->create([
            'changed_by' => $changedBy,
            'old_amount' => $ticket->refund_amount,
            'new_amount' => $newAmount,
            'reason' => $reason,
        ]);

        $ticket->update(['refund_amount' => $newAmount]);
    });

    return $ticket->fresh(['amountLogs']);
}




    public function approve(
        Refund $refund,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {


        $currentStatus = $refund->current_status instanceof RefundStatus
            ? $refund->current_status
            : RefundStatus::from($refund->current_status);



        $nextStatus = $currentStatus->next();



        if (! $nextStatus) {

            throw new InvalidArgumentException(
                'This refund cannot be approved.'
            );

        }



        return $this->changeStatus(
            $refund,
            $nextStatus,
            $note,
            $changedBy
        );

    }



    public function returnBack(
        Refund $refund,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {


        $currentStatus = $refund->current_status instanceof RefundStatus
            ? $refund->current_status
            : RefundStatus::from($refund->current_status);



        $previousStatus = $currentStatus->returnedTo();



        if (! $previousStatus) {

            throw new InvalidArgumentException(
                'This refund cannot be returned.'
            );

        }



        return $this->changeStatus(
            $refund,
            $previousStatus,
            $note,
            $changedBy
        );

    }



    public function reject(
        Refund $refund,
        string $reason,
        ?int $changedBy = null
    ): Refund {


        return $this->changeStatus(
            $refund,
            RefundStatus::REJECTED,
            $reason,
            $changedBy
        );

    }



    public function cancel(
        Refund $refund,
        string $reason,
        ?int $changedBy = null
    ): Refund {


        return $this->changeStatus(
            $refund,
            RefundStatus::CANCELLED,
            $reason,
            $changedBy
        );

    }



    public function complete(
        Refund $refund,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {


        return $this->changeStatus(
            $refund,
            RefundStatus::REFUND_COMPLETED,
            $note,
            $changedBy
        );

    }

}