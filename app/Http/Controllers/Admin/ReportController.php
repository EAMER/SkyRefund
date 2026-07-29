<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function export(Request $request)
    {

        $request->validate([

            'type'=>[
                'nullable',
                'in:daily,weekly,monthly,airline,department,resolution'
            ],

            'from'=>[
                'nullable',
                'date'
            ],

            'to'=>[
                'nullable',
                'date',
                'after_or_equal:from'
            ],

        ]);



        $user=$request->user();



        $type=$request->get(
            'type',
            'daily'
        );


        $query=Refund::query();



        /*
        |--------------------------------------------------------------------------
        | Tenant Isolation
        |--------------------------------------------------------------------------
        */


        if(
            ! $user->isSuperAdmin()
        ){

            $query->where(
                'airline_id',
                $user->airline_id
            );

        }




        if($request->filled('from')){

            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );

        }



        if($request->filled('to')){

            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );

        }





        $rows = match($type){


            'weekly'=>

                $query->select(

                    DB::raw(
                        'YEAR(created_at) year'
                    ),

                    DB::raw(
                        'WEEK(created_at,1) week'
                    ),

                    DB::raw(
                        'COUNT(*) total_refunds'
                    )

                )

                ->groupBy(
                    'year',
                    'week'
                )

                ->get(),




            'monthly'=>

                $query->select(

                    DB::raw(
                        'DATE_FORMAT(created_at,"%Y-%m") month'
                    ),

                    DB::raw(
                        'COUNT(*) total_refunds'
                    )

                )

                ->groupBy(
                    'month'
                )

                ->get(),





            'airline'=>

                $query->select(

                    'airline_id',

                    DB::raw(
                        'COUNT(*) total_refunds'
                    )

                )

                ->with('airline')

                ->groupBy(
                    'airline_id'
                )

                ->get(),




            'department'=>

                $query->select(

                    'current_department',

                    DB::raw(
                        'COUNT(*) total_refunds'
                    )

                )

                ->groupBy(
                    'current_department'
                )

                ->get(),




            'resolution'=>

                $query->select(

                    DB::raw(
                    'AVG(
                        TIMESTAMPDIFF(
                            MINUTE,
                            created_at,
                            updated_at
                        )
                    ) avg_resolution_minutes'
                    )

                )

                ->first(),




            default =>

                $query->select(

                    DB::raw(
                        'DATE(created_at) day'
                    ),

                    DB::raw(
                        'COUNT(*) total_refunds'
                    )

                )

                ->groupBy('day')

                ->get()

        };





        $filename =
            "refund-report-{$type}-"
            .now()->format('YmdHis')
            .".csv";





        return response()->streamDownload(

            function() use($rows,$type){

                $handle=fopen(
                    'php://output',
                    'w'
                );


                $safe=function($value){

                    $value=(string)$value;


                    if(
                        preg_match(
                            '/^[=\+\-@]/',
                            $value
                        )
                    ){

                        return "'".$value;

                    }


                    return $value;

                };



                if($type==='resolution'){

                    fputcsv(
                        $handle,
                        [
                            'avg_resolution_minutes'
                        ]
                    );


                    fputcsv(
                        $handle,
                        [
                            $rows->avg_resolution_minutes ?? 0
                        ]
                    );

                }



                else {

                    foreach(
                        $rows as $row
                    ){

                        fputcsv(
                            $handle,
                            array_map(
                                $safe,
                                (array)$row
                            )
                        );

                    }

                }



                fclose($handle);


            },

            $filename,

            [
                'Content-Type'=>
                'text/csv'
            ]

        );

    }

}