<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequest;
use App\Jobs\ProcessRefundAi;
use App\Enums\Department;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\RefundStatus;
use App\Models\Refund;
use App\Services\AttachmentService;
use App\Services\RefundNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    
    public function __construct(
        protected AttachmentService $attachmentService,
        protected RefundNotificationService $notificationService
    ) {
    }


    public function store(StoreRefundRequest $request)
    {
        try {

            $refund = DB::transaction(function () use ($request) {

                $refund = Refund::create([

                    'reference' => $this->generateReference(),

                    'airline_id' => $request->airline_id,

                    'created_by' => Auth::id(),

                    'first_name' => $request->first_name,

                    'last_name' => $request->last_name,

                    'email' => $request->email,

                    'phone' => $request->phone,

                    'address' => $request->address,


                    'refund_reason' => $request->refund_reason,

                    'refund_type' => $request->refund_type,

                    'passenger_explanation' =>
                        $request->passenger_explanation,


                    'bank_name' => $request->bank_name,

                    'account_name' => $request->account_name,

                    'account_number' => $request->account_number,

                    'account_type' => $request->account_type,


                    'current_status' =>
                        RefundStatus::NEW_REQUEST,

                    'current_department' =>
                        Department::REFUND,

                    'priority' =>
                        Priority::LOW,


                    'consent' =>
                        $request->consent,

                ]);



                foreach ($request->tickets as $ticket) {

                    $refund->tickets()->create([

                        'booking_reference' =>
                            $ticket['booking_reference'],

                        'ticket_number' =>
                            $ticket['ticket_number'],

                        'passenger_name' =>
                            $ticket['passenger_name'],

                        'flight_number' =>
                            $ticket['flight_number'],

                        'airline_code' =>
                            $ticket['airline_code'],

                        'origin_airport' =>
                            $ticket['origin_airport'],

                        'destination_airport' =>
                            $ticket['destination_airport'],

                        'departure_datetime' =>
                            $ticket['departure_datetime'],

                        'remarks' =>
                            $ticket['remarks'] ?? null,

                        'ticket_status' =>
                            TicketStatus::CONFIRMED,

                    ]);
                }



                $this->attachmentService->store(
                    $refund,
                    $request
                );



                $refund->statusLogs()->create([

                    'changed_by' =>
                        Auth::id(),

                    'old_status' =>
                        null,

                    'new_status' =>
                        RefundStatus::NEW_REQUEST,

                    'note' =>
                        'Refund request submitted by passenger.',

                ]);



                return $refund;

            });



            /*
            |--------------------------------------------------------------------------
            | Queue actions after database commit
            |--------------------------------------------------------------------------
            */


            DB::afterCommit(function () use ($refund) {


                $this->notificationService
                    ->sendSubmissionNotification($refund);



                ProcessRefundAi::dispatch(
                    $refund->fresh()
                );


            });



            return response()->json([

                'success' => true,

                'message' =>
                    'Refund submitted successfully.',

                'reference' =>
                    $refund->reference,


                'refund' =>
                    $refund->load([
                        'airline',
                        'tickets',
                        'attachments',
                        'statusLogs',
                    ]),


            ], 201);



        } catch (\Throwable $e) {


            return response()->json([

                'success' => false,

                'message' =>
                    'Unable to create refund.',

                'error' =>
                    config('app.debug')
                        ? $e->getMessage()
                        : 'An unexpected error occurred.',

            ], 500);

        }
    }



    private function generateReference(): string
    {
        do {

            $reference =
                'SR-' .
                now()->format('Ymd') .
                '-' .
                str_pad(
                    random_int(1, 999999),
                    6,
                    '0',
                    STR_PAD_LEFT
                );


        } while (
            Refund::where(
                'reference',
                $reference
            )->exists()
        );


        return $reference;
    }
}