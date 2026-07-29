<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAirlineTenant
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {


        $user = $request->user();



        /*
        |--------------------------------------------------------------------------
        | Authentication Check
        |--------------------------------------------------------------------------
        */


        if (! $user) {

            return response()->json([

                'success'=>false,

                'message'=>'Unauthenticated.'

            ],401);

        }




        /*
        |--------------------------------------------------------------------------
        | Super Admin Bypass
        |--------------------------------------------------------------------------
        |
        | Super admins manage all airlines.
        |
        */


        if ($user->isSuperAdmin()) {

            return $next($request);

        }




        /*
        |--------------------------------------------------------------------------
        | Normal User Airline Check
        |--------------------------------------------------------------------------
        */


        if (! $user->airline_id) {


            return response()->json([

                'success'=>false,

                'message'=>'User is not assigned to an airline.'

            ],403);

        }





        /*
        |--------------------------------------------------------------------------
        | Prevent Cross Airline Access
        |--------------------------------------------------------------------------
        */


        $airlineId =
            $request->route('airline')
            ??
            $request->input('airline_id');



        if (

            $airlineId

            &&

            (int)$airlineId !== (int)$user->airline_id

        ) {


            return response()->json([

                'success'=>false,

                'message'=>'You cannot access another airline.'

            ],403);


        }




        return $next($request);

    }
}