<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{


    /**
     * Login user.
     */
    public function login(LoginRequest $request)
    {


        if (
            ! Auth::attempt(
                $request->only(
                    'email',
                    'password'
                )
            )
        ) {

            return response()->json([

                'success'=>false,

                'message'=>'Invalid credentials.'

            ],401);

        }




        /** @var \App\Models\User $user */
        $user = Auth::user();




        /*
        |--------------------------------------------------------------------------
        | Active Account Check
        |--------------------------------------------------------------------------
        */


        if (! $user->active) {


            $user
                ->tokens()
                ->delete();


            return response()->json([

                'success'=>false,

                'message'=>'Your account has been disabled.'

            ],403);


        }





        /*
        |--------------------------------------------------------------------------
        | Single Session
        |--------------------------------------------------------------------------
        */


        $user
            ->tokens()
            ->delete();





        $token =
            $user
            ->createToken(
                'SkyRefund'
            )
            ->plainTextToken;





        return response()->json([

            'success'=>true,

            'message'=>'Login successful.',


            'token'=>$token,


            'user'=>$this->userResponse($user)

        ]);

    }





    /**
     * Logout current token.
     */
    public function logout(Request $request)
    {


        $token =
            $request
            ->user()
            ?->currentAccessToken();



        if($token){

            $token->delete();

        }



        return response()->json([

            'success'=>true,

            'message'=>'Logged out successfully.'

        ]);

    }





    /**
     * Current authenticated user.
     */
    public function me(Request $request)
    {

        return response()->json([

            'success'=>true,

            'user'=>
                $this->userResponse(
                    $request->user()
                )

        ]);

    }





    /**
     * User API response format.
     */
    private function userResponse($user): array
    {

        return [

            'id'=>$user->id,

            'name'=>$user->name,

            'email'=>$user->email,


            'role'=>
                $user->role?->value,


            'department'=>
                $user->department?->value,


            'airline_id'=>
                $user->airline_id,


            'active'=>
                $user->active,

        ];

    }

}