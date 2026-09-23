<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Throwable;

class RefundLookupController extends Controller
{
    public function __construct(
        protected AttachmentService $attachmentService
    ) {
    }


    /**
     * Public status lookup — reference + email must both match, so a
     * reference number alone (which is sequential-ish and guessable)
     * isn't enough to see someone else's refund. Only safe, passenger-
     * facing fields are returned: no admin_notes, no AI risk fields,
     * no bank details, and status-log notes are stripped since those may
     * contain internal department comments not meant for the passenger.
     */
    public function show(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $refund = Refund::where('reference', $data['reference'])
            ->where('email', $data['email'])
            ->with(['tickets', 'attachments', 'statusLogs'])
            ->first();

        if (! $refund) {
            return response()->json([
                'success' => false,
                'message' => 'No refund found matching that reference and email.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'refund' => [
                'reference' => $refund->reference,
                'status' => $refund->current_status->value,
                'status_label' => $refund->current_status->label(),
                'submitted_at' => $refund->created_at,

                'tickets' => $refund->tickets->map(fn ($t) => [
                    'ticket_number' => $t->ticket_number,
                    'booking_reference' => $t->booking_reference,
                    'flight_number' => $t->flight_number,
                    'route' => $t->route(),
                    'refund_amount' => $t->refund_amount,
                    'currency' => $t->currency,
                ]),

                'timeline' => $refund->statusLogs
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn ($log) => [
                        'status' => $log->new_status,
                        'at' => $log->created_at,
                    ]),

                'attachments' => $refund->attachments->map(fn ($a) => [
                    'id' => $a->id,
                    'name' => $a->original_name,
                    'type' => $a->type,
                    'uploaded_at' => $a->created_at,
                ]),
            ],
        ]);
    }


    /**
     * Add supporting documents to an existing refund. Same email
     * verification as show(), same AttachmentService::store() used
     * everywhere else so storage/validation/naming stay consistent.
     */
    public function uploadDocument(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string'],
            'email' => ['required', 'email'],
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => ['file', 'max:10240'],
            'attachment_types' => ['required', 'array'],
            'attachment_types.*' => ['string'],
        ]);

        $refund = Refund::where('reference', $data['reference'])
            ->where('email', $data['email'])
            ->first();

        if (! $refund) {
            return response()->json([
                'success' => false,
                'message' => 'No refund found matching that reference and email.',
            ], 404);
        }

        try {
            $this->attachmentService->store($refund, $request);

            return response()->json([
                'success' => true,
                'message' => 'Document(s) uploaded successfully.',
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}