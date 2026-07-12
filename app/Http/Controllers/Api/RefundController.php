<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequest;
use App\Jobs\ProcessRefundAi;
use App\Models\Refund;
use App\Services\AttachmentService;
use App\Services\RefundNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AttachmentService $attachmentService,
        protected RefundNotificationService $notificationService
    ) {
        //
    }

    /**
     * Store a newly created refund request.
     */
    public function store(StoreRefundRequest $request)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Create Refund
            |--------------------------------------------------------------------------
            */

            $refund = Refund::create([

                'reference' => $this->generateReference(),

                'airline_id' => $request->airline_id,

                'first_name' => $request->first_name,

                'last_name' => $request->last_name,

                'email' => $request->email,

                'phone' => $request->phone,

                'address' => $request->address,

                'refund_reason' => $request->refund_reason,

                'refund_type' => $request->refund_type,

                'passenger_explanation' => $request->passenger_explanation,

                'bank_name' => $request->bank_name,

                'account_name' => $request->account_name,

                'account_number' => $request->account_number,

                'account_type' => $request->account_type,

                'consent' => $request->consent,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Ticket(s)
            |--------------------------------------------------------------------------
            */

            foreach ($request->tickets as $ticket) {

                $refund->tickets()->create([

                    'booking_reference'   => $ticket['booking_reference'],

                    'ticket_number'       => $ticket['ticket_number'],

                    'passenger_name'      => $ticket['passenger_name'],

                    'flight_number'       => $ticket['flight_number'],

                    'airline_code'        => $ticket['airline_code'],

                    'origin_airport'      => $ticket['origin_airport'],

                    'destination_airport' => $ticket['destination_airport'],

                    'departure_datetime'  => $ticket['departure_datetime'],

                    'remarks'             => $ticket['remarks'] ?? null,

                    'ticket_status'       => 'PENDING',

                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Store Attachments
            |--------------------------------------------------------------------------
            */

            $this->attachmentService->store(
                $refund,
                $request
            );

            /*
            |--------------------------------------------------------------------------
            | Create Initial Status Log
            |--------------------------------------------------------------------------
            */

            $refund->statusLogs()->create([

                'changed_by' => Auth::id(),

                'old_status' => null,

                'new_status' => 'NEW_REQUEST',

                'note' => 'Refund request submitted by passenger.',

            ]);

            $this->notificationService->sendSubmissionNotification($refund);

            DB::afterCommit(function () use ($refund) {
                Bus::dispatch(new ProcessRefundAi($refund));
            });

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'Refund submitted successfully.',

                'reference' => $refund->reference,

                'refund' => $refund->load([
                    'airline',
                    'tickets',
                    'attachments',
                    'statusLogs',
                ]),

            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => 'Unable to create refund.',

                'error' => config('app.debug')
                    ? $e->getMessage()
                    : 'An unexpected error occurred.',

            ], 500);
        }
    }

    /**
     * Generate a unique refund reference.
     */
    private function generateReference(): string
    {
        do {

            $reference = 'SR-' . now()->format('Ymd') . '-' . str_pad(
                random_int(1, 999999),
                6,
                '0',
                STR_PAD_LEFT
            );

        } while (Refund::where('reference', $reference)->exists());

        return $reference;
    }
}