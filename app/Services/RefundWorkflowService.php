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

                RefundStatus::PENDING_COMMERCIAL =>
                    Department::COMMERCIAL,

                RefundStatus::PENDING_AUDIT =>
                    Department::AUDIT,

                RefundStatus::PENDING_FINANCE =>
                    Department::FINANCE,

                RefundStatus::PENDING_TREASURY =>
                    Department::TREASURY,

                // A return always lands back with the Refund Officer for
                // correction, regardless of which department sent it back.
                RefundStatus::RETURNED_BY_COMMERCIAL,
                RefundStatus::RETURNED_BY_AUDIT,
                RefundStatus::RETURNED_BY_FINANCE =>
                    Department::REFUND,

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


    /**
     * Saves the Refund Officer's per-ticket calculation. If $submit is true,
     * also advances the refund — from NEW_REQUEST to PENDING_COMMERCIAL on
     * first submission, or from a RETURNED_BY_* status back to whichever
     * PENDING_* stage returned it, on resubmission after a correction.
     * $submit=false is "Save Draft" and never changes the status.
     *
     * @param array<int, array{ticket_id:int, fare_paid:float, nuc:float,
     *   government_tax_ng:float, security_tax_yq:float, airport_tax_qt:float,
     *   insurance:float, is_no_show:bool, no_show_fee:float}> $ticketsPayload
     */
    public function submitCalculation(
        Refund $refund,
        array $ticketsPayload,
        bool $submit,
        ?string $note = null,
        ?int $changedBy = null
    ): Refund {

        $currentStatus = $refund->current_status instanceof RefundStatus
            ? $refund->current_status
            : RefundStatus::from($refund->current_status);

        $submitTarget = match ($currentStatus) {
            RefundStatus::NEW_REQUEST => RefundStatus::PENDING_COMMERCIAL,
            RefundStatus::RETURNED_BY_COMMERCIAL => RefundStatus::PENDING_COMMERCIAL,
            RefundStatus::RETURNED_BY_AUDIT => RefundStatus::PENDING_AUDIT,
            RefundStatus::RETURNED_BY_FINANCE => RefundStatus::PENDING_FINANCE,
            default => null,
        };

        if ($submit && ! $submitTarget) {
            throw new InvalidArgumentException(
                'Calculations can only be submitted while the refund is new or returned for correction.'
            );
        }

        DB::transaction(function () use ($refund, $ticketsPayload) {

            $ticketIds = collect($ticketsPayload)->pluck('ticket_id');

            $tickets = $refund->tickets()
                ->whereIn('id', $ticketIds)
                ->get()
                ->keyBy('id');

            foreach ($ticketsPayload as $row) {

                $ticket = $tickets->get($row['ticket_id']);

                if (! $ticket) {
                    throw new InvalidArgumentException(
                        "Ticket {$row['ticket_id']} does not belong to this refund."
                    );
                }

                $ticket->fill([
                    'fare_paid' => $row['fare_paid'],
                    'nuc' => $row['nuc'],
                    'government_tax_ng' => $row['government_tax_ng'],
                    'security_tax_yq' => $row['security_tax_yq'],
                    'airport_tax_qt' => $row['airport_tax_qt'],
                    'insurance' => $row['insurance'],
                    'is_no_show' => $row['is_no_show'],
                    'no_show_fee' => $row['no_show_fee'],
                ]);

                $ticket->recalculateDeduction();
                $ticket->save();
            }
        });

        if (! $submit) {
            return $refund->fresh(['tickets']);
        }

        return $this->changeStatus($refund, $submitTarget, $note, $changedBy);
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


    /**
     * Sends a refund back to the Refund Officer for correction. Derives the
     * RETURNED_BY_* target from the current PENDING_* stage — NOT via
     * RefundStatus::returnedTo(), which only walks the other direction
     * (RETURNED_BY_* back to the previous PENDING_* stage on resubmission,
     * handled in submitCalculation() above). Calling returnedTo() here was
     * the original bug: it's null for every PENDING_* status, so this
     * always threw "This refund cannot be returned."
     */
    public function returnBack(
    Refund $refund,
    ?string $note = null,
    ?int $changedBy = null
    ): 
    Refund {
 
    $currentStatus = $refund->current_status instanceof RefundStatus
        ? $refund->current_status
        : RefundStatus::from($refund->current_status);
 
    $returnStatus = match ($currentStatus) {
        RefundStatus::PENDING_COMMERCIAL => RefundStatus::RETURNED_BY_COMMERCIAL,
        RefundStatus::PENDING_AUDIT => RefundStatus::RETURNED_BY_AUDIT,
        RefundStatus::PENDING_FINANCE => RefundStatus::RETURNED_BY_FINANCE,
        default => null,
    };
 
    if (! $returnStatus) {
        throw new InvalidArgumentException(
            'This refund cannot be returned from its current stage.'
        );
    }
 
    if ($changedBy) {
        $refund->update(['assigned_to' => $changedBy]);
    }
 
    return $this->changeStatus($refund, $returnStatus, $note, $changedBy);
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
    string $paymentReference,
    ?\DateTimeInterface $paidAt = null,
    ?string $note = null,
    ?int $changedBy = null
    ):  
    Refund {
 
    $refund->update([
        'payment_reference' => $paymentReference,
        'paid_at' => $paidAt ?? now(),
    ]);
 
    return $this->changeStatus(
        $refund,
        RefundStatus::REFUND_COMPLETED,
        $note,
        $changedBy
    );
    }

}