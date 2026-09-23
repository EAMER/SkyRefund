<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTicketAmountRequest;
use App\Http\Requests\SubmitTicketCalculationRequest;
use App\Models\Refund;
use App\Models\User;
use App\Models\RefundTicket;
use App\Services\RefundWorkflowService;
use App\Services\AttachmentService;
use App\Enums\Priority;
use App\Enums\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Throwable;

class RefundActionController extends Controller
{
    public function __construct(
        protected RefundWorkflowService $workflow,
        protected AttachmentService $attachmentService
    ) {
    }



    private function responseRefund(Refund $refund)
    {
        return $refund->fresh([
            'airline',
            'tickets',
            'attachments',
            'statusLogs',
            'assignee',
        ]);
    }



    private function authorizeAction(
        Request $request,
        Refund $refund,
        string $ability
    ): void {

        if (! $request->user()?->can($ability, $refund)) {

            abort(
                403,
                'You are not authorized to perform this action.'
            );
        }
    }




    public function assign(
        Request $request,
        Refund $refund
    ) {

        $this->authorizeAction(
            $request,
            $refund,
            'assign'
        );


        $data = $request->validate([

            'assigned_to' => [
                'required',
                'exists:users,id'
            ],

        ]);



        $user = User::findOrFail(
            $data['assigned_to']
        );



        if (
            $user->airline_id !== $refund->airline_id
            &&
            ! $request->user()->isSuperAdmin()
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Cannot assign refund to another airline user.'

            ],403);
        }



        $refund->update([

            'assigned_to' =>
                $user->id

        ]);



        return response()->json([

            'success'=>true,

            'message'=>'Refund assigned successfully.',

            'refund'=>
                $this->responseRefund($refund)

        ]);
    }



    public function saveCalculationDraft(
        SubmitTicketCalculationRequest $request,
        Refund $refund
    ) {
        $this->authorizeAction($request, $refund, 'submitCalculation');

        try {

            $refund = $this->workflow->submitCalculation(
                $refund,
                $request->validated()['tickets'],
                submit: false,
                changedBy: Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Calculation saved as draft.',
                'refund' => $this->responseRefund($refund),
            ]);

        } catch (Throwable $e) {
            return $this->error($e);
        }
    }



    public function submitCalculation(
        SubmitTicketCalculationRequest $request,
        Refund $refund
    ) {
        $this->authorizeAction($request, $refund, 'submitCalculation');

        try {

            $refund = $this->workflow->submitCalculation(
                $refund,
                $request->validated()['tickets'],
                submit: true,
                note: $request->validated()['note'] ?? null,
                changedBy: Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => "Calculation submitted to {$refund->current_department->label()}.",
                'refund' => $this->responseRefund($refund),
            ]);

        } catch (Throwable $e) {
            return $this->error($e);
        }
    }



    public function updateTicketAmount(
        UpdateTicketAmountRequest $request,
        Refund $refund,
        RefundTicket $ticket
    ) {

        $this->authorizeAction(
            $request,
            $refund,
            'updateTicketAmount'
        );

        if ($ticket->refund_id !== $refund->id) {
            abort(404, 'Ticket does not belong to this refund.');
        }

        $data = $request->validated();

        try {

            $ticket = $this->workflow->adjustTicketAmount(
                $ticket,
                (float) $data['amount'],
                $data['reason'],
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund amount updated.',
                'ticket' => $ticket,
            ]);

        } catch (Throwable $e) {

            return $this->error($e);
        }
    }



    /**
     * Adds new supporting documents to an existing refund — used during
     * the officer's initial review and again during post-return correction.
     * Delegates to the same AttachmentService::store() used at passenger
     * submission time, so storage/validation/naming stay identical.
     */
    public function uploadAttachments(
        Request $request,
        Refund $refund
    ) {

        $this->authorizeAction(
            $request,
            $refund,
            'uploadAttachment'
        );

        $request->validate([
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => ['file', 'max:10240'],
            'attachment_types' => ['required', 'array'],
            'attachment_types.*' => ['string'],
        ]);

        try {

            $this->attachmentService->store($refund, $request);

            return response()->json([
                'success' => true,
                'message' => 'Attachments uploaded.',
                'refund' => $this->responseRefund($refund),
            ]);

        } catch (Throwable $e) {

            return $this->error($e);
        }
    }



    public function approve(
        Request $request,
        Refund $refund
    ) {

        return $this->workflowAction(
            $request,
            $refund,
            'approve'
        );
    }



    public function returnBack(
        Request $request,
        Refund $refund
    ) {

        $data = $request->validate([

            'note' => [
                'required',
                'string'
            ]

        ]);


        $this->authorizeAction(
            $request,
            $refund,
            'returnBack'
        );


        try {

            $refund =
                $this->workflow->returnBack(
                    $refund,
                    $data['note'],
                    Auth::id()
                );


            return response()->json([

                'success' => true,

                'message' => 'Refund returned successfully.',

                'refund' => $refund

            ]);


        } catch (Throwable $e) {

            return $this->error($e);
        }
    }



    public function reject(
        Request $request,
        Refund $refund
    ) {

        $data = $request->validate([

            'reason' => [
                'required',
                'string'
            ]

        ]);


        $this->authorizeAction(
            $request,
            $refund,
            'reject'
        );


        return $this->workflowAction(
            $request,
            $refund,
            'reject',
            $data['reason']
        );
    }



    public function cancel(
        Request $request,
        Refund $refund
    ) {

        $data = $request->validate([

            'reason' => [
                'required',
                'string'
            ]

        ]);


        $this->authorizeAction(
            $request,
            $refund,
            'cancel'
        );


        try {

            $refund =
                $this->workflow->cancel(
                    $refund,
                    $data['reason'],
                    Auth::id()
                );


            return response()->json([

                'success' => true,

                'message' => 'Refund cancelled.',

                'refund' => $refund

            ]);

        } catch (Throwable $e) {

            return $this->error($e);
        }
    }



    /**
     * Marks a refund complete. Requires a payment reference (UC-06:
     * treasury pays externally, then records the reference/date here)
     * rather than being a bare status flip — handled directly rather
     * than through workflowAction() since it needs its own validation.
     */
    public function complete(
        Request $request,
        Refund $refund
    ) {

        $data = $request->validate([
            'payment_reference' => ['required', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $this->authorizeAction(
            $request,
            $refund,
            'complete'
        );

        try {

            $refund = $this->workflow->complete(
                $refund,
                $data['payment_reference'],
                isset($data['paid_at']) ? \Carbon\Carbon::parse($data['paid_at']) : null,
                $data['note'] ?? null,
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund completed.',
                'refund' => $this->responseRefund($refund),
            ]);

        } catch (Throwable $e) {

            return $this->error($e);
        }
    }



    /**
     * Manual admin override to reassign current_department directly,
     * bypassing the normal status-driven workflow. Gated to SUPER_ADMIN
     * only via RefundPolicy::updateDepartment(). current_status is left
     * unchanged — this is not a workflow transition — and the reassignment
     * is logged into the existing statusLogs audit trail so it still shows
     * up in the refund's activity feed.
     */
    public function updateDepartment(
        Request $request,
        Refund $refund
    ) {

        $this->authorizeAction(
            $request,
            $refund,
            'updateDepartment'
        );

        $data = $request->validate([
            'department' => ['required', new Enum(Department::class)],
        ]);

        $newDepartment = Department::from($data['department']);
        $oldDepartment = $refund->current_department;

        if ($oldDepartment === $newDepartment) {
            return response()->json([
                'success' => false,
                'message' => "Refund is already assigned to {$newDepartment->label()}.",
            ], 422);
        }

        DB::transaction(function () use ($refund, $oldDepartment, $newDepartment, $request) {

            $refund->update([
                'current_department' => $newDepartment,
            ]);

            $refund->statusLogs()->create([
                'changed_by' => Auth::id(),
                'old_status' => $refund->current_status->value,
                'new_status' => $refund->current_status->value,
                'note' => "Department manually reassigned: {$oldDepartment->label()} → {$newDepartment->label()}"
                    . ($request->filled('reason') ? ' — ' . $request->input('reason') : ''),
            ]);

        });

        return response()->json([
            'success' => true,
            'message' => 'Department reassigned.',
            'refund' => $this->responseRefund($refund),
        ]);
    }



    /**
     * Edits the internal admin_notes field on the refund. Same authorization
     * as approve() (whichever department currently owns it, or super admin) —
     * lower risk than updateDepartment since it doesn't change workflow state.
     */
    public function updateNotes(
        Request $request,
        Refund $refund
    ) {

        $this->authorizeAction(
            $request,
            $refund,
            'updateNotes'
        );

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $refund->update([
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notes updated.',
            'refund' => $this->responseRefund($refund),
        ]);
    }



    private function workflowAction(
        Request $request,
        Refund $refund,
        string $action,
        ?string $note = null
    ) {

        $this->authorizeAction(
            $request,
            $refund,
            $action
        );


        try {

            $result =
                match ($action) {

                    'approve' =>
                        $this->workflow->approve(
                            $refund,
                            $request->note,
                            Auth::id()
                        ),


                    'reject' =>
                        $this->workflow->reject(
                            $refund,
                            $note,
                            Auth::id()
                        ),

                };



            return response()->json([

                'success' => true,

                'message' => "Refund {$action} successful.",

                'refund' => $result

            ]);

        } catch (Throwable $e) {

            return $this->error($e);
        }
    }



    private function error(Throwable $e)
    {

        return response()->json([

            'success' => false,

            'message' => $e->getMessage()

        ], 422);
    }
}