<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Services\RefundWorkflowService;
use App\Enums\Priority;
use App\Enums\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class RefundActionController extends Controller
{
    public function __construct(
        protected RefundWorkflowService $workflow
    ) {
    }

    /**
     * Assign refund to an admin.
     */
    public function assign(Request $request, Refund $refund)
    {
        $request->validate([
            'assigned_to' => ['required', 'integer'],
        ]);

        if ($refund->assigned_to == $request->assigned_to) {
            return response()->json([
                'success' => false,
                'message' => 'Refund is already assigned to this admin.',
            ], 422);
        }

        $refund->update([
            'assigned_to' => $request->assigned_to,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund assigned successfully.',
            'refund' => $refund->fresh([
                'airline',
                'tickets',
                'attachments',
                'statusLogs',
            ]),
        ]);
    }

    /**
     * Approve refund and move to next workflow stage.
     */
    public function approve(Request $request, Refund $refund)
    {
        $request->validate([
            'note' => ['nullable', 'string'],
        ]);

        $refund = $this->workflow->approve(
            refund: $refund,
            note: $request->note,
            changedBy: null // auth()->id() later
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund approved successfully.',
            'refund' => $refund,
        ]);
    }

    /**
     * Return refund to previous department.
     */
    public function returnBack(Request $request, Refund $refund)
    {
        $request->validate([
            'note' => ['required', 'string'],
        ]);

        $refund = $this->workflow->returnBack(
            refund: $refund,
            note: $request->note,
            changedBy: null // auth()->id() later
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund returned successfully.',
            'refund' => $refund,
        ]);
    }

    /**
     * Reject refund.
     */
    public function reject(Request $request, Refund $refund)
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $refund = $this->workflow->reject(
            refund: $refund,
            reason: $request->reason,
            changedBy: null // auth()->id() later
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund rejected successfully.',
            'refund' => $refund,
        ]);
    }

    /**
     * Cancel refund.
     */
    public function cancel(Request $request, Refund $refund)
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $refund = $this->workflow->cancel(
            refund: $refund,
            reason: $request->reason,
            changedBy: null // auth()->id() later
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund cancelled successfully.',
            'refund' => $refund,
        ]);
    }

    /**
     * Complete refund.
     */
    public function complete(Request $request, Refund $refund)
    {
        $request->validate([
            'note' => ['nullable', 'string'],
        ]);

        $refund = $this->workflow->complete(
            refund: $refund,
            note: $request->note,
            changedBy: null // auth()->id() later
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund completed successfully.',
            'refund' => $refund,
        ]);
    }

    /**
     * Update priority.
     */
    public function updatePriority(Request $request, Refund $refund)
    {
        $request->validate([
            'priority' => [
                'required',
                new Enum(Priority::class),
            ],
        ]);

        $refund->update([
            'priority' => $request->priority,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully.',
            'refund' => $refund->fresh([
                'airline',
                'tickets',
                'attachments',
                'statusLogs',
            ]),
        ]);
    }

    /**
     * Update department.
     */
    public function updateDepartment(Request $request, Refund $refund)
    {
        $request->validate([
            'department' => [
                'required',
                new Enum(Department::class),
            ],
        ]);

        $refund->update([
            'current_department' => $request->department,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully.',
            'refund' => $refund->fresh([
                'airline',
                'tickets',
                'attachments',
                'statusLogs',
            ]),
        ]);
    }

    /**
     * Update admin notes.
     */
    public function updateNotes(Request $request, Refund $refund)
    {
        $request->validate([
            'admin_notes' => [
                'required',
                'string',
            ],
        ]);

        $refund->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin notes updated successfully.',
            'refund' => $refund->fresh([
                'airline',
                'tickets',
                'attachments',
                'statusLogs',
            ]),
        ]);
    }
}