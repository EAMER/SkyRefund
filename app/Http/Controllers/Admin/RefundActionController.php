<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Enums\RefundStatus;
use App\Enums\Priority;
use App\Enums\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class RefundActionController extends Controller
{
    /**
     * Assign a refund to an admin.
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
     * Update refund status.
     */
    public function updateStatus(Request $request, Refund $refund)
    {
        $request->validate([
            'status' => [
                'required',
                new Enum(RefundStatus::class),
            ],
            'note' => [
                'nullable',
                'string',
            ],
        ]);

        DB::transaction(function () use ($request, $refund) {

            $oldStatus = $refund->current_status;

            $refund->update([
                'current_status' => $request->status,
            ]);

            $refund->statusLogs()->create([
                'changed_by' => null, // auth()->id() later
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'note' => $request->note,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Refund status updated successfully.',
            'refund' => $refund->fresh([
                'airline',
                'tickets',
                'attachments',
                'statusLogs',
            ]),
        ]);
    }

    /**
     * Update refund priority.
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
     * Update current department.
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
     * Update internal admin notes.
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