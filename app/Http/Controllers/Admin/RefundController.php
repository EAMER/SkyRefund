<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Enums\UserRole;
use App\Enums\Department;
use Illuminate\Http\Request;

class RefundController extends Controller
{

    /**
     * Display paginated refunds.
     */
    public function index(Request $request)
    {

        $user = $request->user();


        $query = Refund::query()
            ->with([
                'airline',
                'assignee'
            ]);



        /*
        |--------------------------------------------------------------------------
        | Tenant Isolation
        |--------------------------------------------------------------------------
        */


        if (! $user->isSuperAdmin()) {

            $query->where(
                'airline_id',
                $user->airline_id
            );

        }



        /*
        |--------------------------------------------------------------------------
        | Department Visibility
        |--------------------------------------------------------------------------
        | Refund officers and super admins see every refund regardless of stage.
        | Every other role (commercial, audit, finance, treasury) only sees
        | refunds currently sitting in their own department's queue.
        */


        if (
            ! $user->isSuperAdmin()
            &&
            ! $user->hasRole(UserRole::REFUND_OFFICER)
        ) {

            $departmentForRole = match (true) {

                $user->hasRole(UserRole::COMMERCIAL) => Department::COMMERCIAL,
                $user->hasRole(UserRole::AUDIT) => Department::AUDIT,
                $user->hasRole(UserRole::FINANCE) => Department::FINANCE,
                $user->hasRole(UserRole::TREASURY) => Department::TREASURY,

                default => null,

            };


            if ($departmentForRole) {

                $query->where(
                    'current_department',
                    $departmentForRole
                );

            } else {

                // Role isn't recognized for department scoping — show nothing
                // rather than accidentally leaking every refund.
                $query->whereRaw('1 = 0');

            }

        }



        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */


        if ($request->filled('search')) {

            $search = $request->search;


            $query->where(function($q) use ($search){

                $q->where(
                    'reference',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'first_name',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'last_name',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'email',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'phone',
                    'like',
                    "%{$search}%"
                );

            });

        }





        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */


        $filters = [

            'current_status',
            'priority',
            'current_department',
            'assigned_to',
            'airline_id',

        ];



        foreach ($filters as $filter) {

            if ($request->filled($filter)) {

                /*
                 Prevent normal users
                 overriding tenant filter
                */

                if (
                    $filter === 'airline_id'
                    &&
                    ! $user->isSuperAdmin()
                ) {

                    continue;

                }


                $query->where(
                    $filter,
                    $request->$filter
                );

            }

        }




        /*
        |--------------------------------------------------------------------------
        | Date Filters
        |--------------------------------------------------------------------------
        */


        if ($request->filled('from')) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );

        }



        if ($request->filled('to')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );

        }




        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */


        $allowedSorts = [

            'created_at',
            'updated_at',
            'priority',
            'current_status'

        ];


        $sort = $request->get(
            'sort',
            'created_at'
        );


        $direction =
            $request->get('direction') === 'asc'
            ? 'asc'
            : 'desc';



        if(
            in_array(
                $sort,
                $allowedSorts,
                true
            )
        ){

            $query->orderBy(
                $sort,
                $direction
            );

        }
        else {

            $query->latest();

        }





        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */


        $refunds =
            $query->paginate(
                min(
                    $request->get('per_page',20),
                    100
                )
            )
            ->withQueryString();



        return response()->json([

            'success'=>true,

            'data'=>$refunds

        ]);

    }





    /**
     * Display refund details.
     */
    public function show(
        Request $request,
        Refund $refund
    ){

        $user=$request->user();



        /*
        |--------------------------------------------------------------------------
        | Tenant Security
        |--------------------------------------------------------------------------
        */


        if(
            ! $user->isSuperAdmin()
            &&
            $refund->airline_id !== $user->airline_id
        ){

            return response()->json([

                'success'=>false,

                'message'=>'Refund not found.'

            ],404);

        }





        $refund->load([

            'airline',

            'tickets',

            'attachments',

            'statusLogs.user',

            'assignee'

        ]);



        return response()->json([

            'success'=>true,

            'refund'=>$refund

        ]);

    }
    

}