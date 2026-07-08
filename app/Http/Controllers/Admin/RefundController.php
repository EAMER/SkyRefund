<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    /**
     * Display a paginated list of refunds with filters.
     */
    public function index(Request $request)
    {
        $query = Refund::query()
            ->with('airline');

        /*
        |--------------------------------------------------------------------------
        | Search by Reference
        |--------------------------------------------------------------------------
        */

        if ($request->filled('reference')) {
            $query->where(
                'reference',
                'like',
                '%' . $request->reference . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Search by Passenger Name
        |--------------------------------------------------------------------------
        */

        if ($request->filled('passenger')) {

            $query->where(function ($q) use ($request) {

                $q->where('first_name', 'like', '%' . $request->passenger . '%')
                  ->orWhere('last_name', 'like', '%' . $request->passenger . '%');

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'current_status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Priority
        |--------------------------------------------------------------------------
        */

        if ($request->filled('priority')) {
            $query->where(
                'priority',
                $request->priority
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Department
        |--------------------------------------------------------------------------
        */

        if ($request->filled('department')) {
            $query->where(
                'current_department',
                $request->department
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Assigned Admin
        |--------------------------------------------------------------------------
        */

        if ($request->filled('assigned_to')) {
            $query->where(
                'assigned_to',
                $request->assigned_to
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Airline
        |--------------------------------------------------------------------------
        */

        if ($request->filled('airline_id')) {
            $query->where(
                'airline_id',
                $request->airline_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Date Range
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
        | Pagination
        |--------------------------------------------------------------------------
        */

        $refunds = $query
            ->latest()
            ->paginate(
                $request->get('per_page', 20)
            )
            ->withQueryString();

        return response()->json($refunds);
    }

    /**
     * Display a single refund.
     */
    public function show(Refund $refund)
    {
        $refund->load([
            'airline',
            'tickets',
            'attachments',
            'statusLogs',
        ]);

        return response()->json($refund);
    }
}