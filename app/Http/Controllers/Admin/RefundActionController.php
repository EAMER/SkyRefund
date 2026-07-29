<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTicketAmountRequest;
use App\Models\Refund;
use App\Models\User;
use App\Models\RefundTicket;
use App\Services\RefundWorkflowService;
use App\Enums\Priority;
use App\Enums\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Throwable;

class RefundActionController extends Controller
{
    public function __construct(
        protected RefundWorkflowService $workflow
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

        $data=$request->validate([

            'note'=>[
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

                'success'=>true,

                'message'=>'Refund returned successfully.',

                'refund'=>$refund

            ]);


        } catch(Throwable $e){

            return $this->error($e);
        }
    }





    public function reject(
        Request $request,
        Refund $refund
    ){

        $data=$request->validate([

            'reason'=>[
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
    ){

        $data=$request->validate([

            'reason'=>[
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

                'success'=>true,

                'message'=>'Refund cancelled.',

                'refund'=>$refund

            ]);

        }catch(Throwable $e){

            return $this->error($e);
        }
    }





    public function complete(
        Request $request,
        Refund $refund
    ){

        $this->authorizeAction(
            $request,
            $refund,
            'complete'
        );


        return $this->workflowAction(
            $request,
            $refund,
            'complete'
        );
    }





    private function workflowAction(
        Request $request,
        Refund $refund,
        string $action,
        ?string $note=null
    ){

        $this->authorizeAction(
            $request,
            $refund,
            $action
        );


        try {

            $result =
                match($action){

                    'approve'=>
                        $this->workflow->approve(
                            $refund,
                            $request->note,
                            Auth::id()
                        ),


                    'reject'=>
                        $this->workflow->reject(
                            $refund,
                            $note,
                            Auth::id()
                        ),


                    'complete'=>
                        $this->workflow->complete(
                            $refund,
                            $request->note,
                            Auth::id()
                        ),

                };



            return response()->json([

                'success'=>true,

                'message'=>"Refund {$action} successful.",

                'refund'=>$result

            ]);

        }catch(Throwable $e){

            return $this->error($e);
        }
    }





    private function error(Throwable $e)
    {

        return response()->json([

            'success'=>false,

            'message'=>$e->getMessage()

        ],422);
    }
}