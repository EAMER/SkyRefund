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
        | Search across reference, passenger, email, phone, and notes
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', '%' . $search . '%')
                  ->orWhere('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('admin_notes', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Priority
        |--------------------------------------------------------------------------
        */

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Department
        |--------------------------------------------------------------------------
        */

        if ($request->filled('department')) {
            $query->where('current_department', $request->department);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Assigned Admin
        |--------------------------------------------------------------------------
        */

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Airline
        |--------------------------------------------------------------------------
        */

        if ($request->filled('airline_id')) {
            $query->where('airline_id', $request->airline_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Date Range
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        /*
        |--------------------------------------------------------------------------
        | Basic Sorting
        |--------------------------------------------------------------------------
        */

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (in_array($sort, ['created_at', 'updated_at', 'priority', 'current_status'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $refunds = $query
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