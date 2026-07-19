<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airline;

class AirlineController extends Controller
{
    /**
     * Return all active airlines.
     */
    public function index()
    {
        return response()->json([

            'success' => true,

            'data' => Airline::query()
                ->where('active', true)
                ->orderBy('name')
                ->get([
                    'id' ,
                    'name' ,
                    'code' ,
                    'logo' ,
                ]),

        ]);
    }
}