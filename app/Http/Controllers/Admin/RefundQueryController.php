<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\RefundQuery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundQueryController extends Controller
{
    /**
     * List queries for a refund — used to populate the query thread in
     * the refund detail view.
     */
    public function index(
        Request $request,
        Refund $refund
    ) {

        $this->authorizeAction($request, $refund, 'view');

        $queries = $refund->queries()
            ->with(['raisedBy', 'directedTo', 'resolvedBy'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'queries' => $queries,
        ]);
    }


    /**
     * Raise a query on a refund, directed at a specific user. Any
     * department can do this — it's a side conversation, not a workflow
     * transition, so it doesn't touch current_status or current_department.
     */
    public function store(
        Request $request,
        Refund $refund
    ) {

        $this->authorizeAction($request, $refund, 'raiseQuery');

        $data = $request->validate([
            'directed_to_user_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $targetUser = User::findOrFail($data['directed_to_user_id']);

        if (
            $targetUser->airline_id !== $refund->airline_id
            && ! $request->user()->isSuperAdmin()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot direct a query to a user outside this airline.',
            ], 403);
        }

        $query = RefundQuery::create([
            'refund_id' => $refund->id,
            'raised_by_user_id' => Auth::id(),
            'directed_to_user_id' => $targetUser->id,
            'message' => $data['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Query sent.',
            'query' => $query->load(['raisedBy', 'directedTo']),
        ], 201);
    }


    /**
     * Respond to and resolve a query. Only the user it was directed to
     * (or a super admin) can resolve it.
     */
    public function resolve(
        Request $request,
        Refund $refund,
        RefundQuery $query
    ) {

        if ($query->refund_id !== $refund->id) {
            abort(404, 'Query does not belong to this refund.');
        }

        $user = $request->user();

        if (
            $query->directed_to_user_id !== $user->id
            && ! $user->isSuperAdmin()
        ) {
            abort(403, 'Only the user this query was directed to can resolve it.');
        }

        if ($query->isResolved()) {
            return response()->json([
                'success' => false,
                'message' => 'This query has already been resolved.',
            ], 422);
        }

        $data = $request->validate([
            'response' => ['required', 'string', 'max:2000'],
        ]);

        $query->update([
            'response' => $data['response'],
            'resolved_by_user_id' => $user->id,
            'resolved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Query resolved.',
            'query' => $query->fresh(['raisedBy', 'directedTo', 'resolvedBy']),
        ]);
    }


    private function authorizeAction(
        Request $request,
        Refund $refund,
        string $ability
    ): void {

        if (! $request->user()?->can($ability, $refund)) {
            abort(403, 'You are not authorized to perform this action.');
        }
    }
}