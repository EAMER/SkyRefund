<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Jobs\SendPasswordResetEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    /**
     * List users. Tenant-scoped unless super admin.
     */
    public function index(Request $request)
    {
        $admin = $request->user();
    
        abort_unless($admin->can('viewAny', User::class), 403);
    
        $query = User::query()
            ->with('airline:id,name')
            ->select([
                'id', 'name', 'email', 'airline_id', 'department', 'role', 'active', 'created_at',
            ]);
    
        if (! $admin->isSuperAdmin()) {
            $query->where('airline_id', $admin->airline_id);
        }
    
        return response()->json([
            'success' => true,
            'data' => $query->orderBy('name')->get(),
        ]);
    }

    /**
     * Update a user's role and/or department.
     */
    public function updateRole(Request $request, User $user)
    {
        $admin = $request->user();

        abort_unless($admin->can('update', $user), 403);

        $data = $request->validate([
            'role' => ['required', new Enum(UserRole::class)],
            'department' => ['nullable', new Enum(Department::class)],
        ]);

        $user->update([
            'role' => $data['role'],
            'department' => $data['department'] ?? $user->department,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated.',
            'user' => $user->fresh(),
        ]);
    }


    /**
     * Activate or deactivate a user account.
     */
    public function toggleActive(Request $request, User $user)
    {
        $admin = $request->user();

        abort_unless($admin->can('update', $user), 403);

        $user->update(['active' => ! $user->active]);

        return response()->json([
            'success' => true,
            'message' => $user->active ? 'User activated.' : 'User deactivated.',
            'user' => $user->fresh(),
        ]);
    }


    /**
     * Reset a user's password to a new temporary password,
     * emailed directly to the user.
     */
    public function resetPassword(Request $request, User $user)
    {
        $admin = $request->user();

        abort_unless($admin->can('resetPassword', $user), 403);

        $temporaryPassword = Str::password(12);

        DB::transaction(function () use ($user, $temporaryPassword) {
            $user->update([
                'password' => Hash::make($temporaryPassword),
            ]);
        });

        DB::afterCommit(function () use ($user, $temporaryPassword) {
            SendPasswordResetEmail::dispatch($user, $temporaryPassword);
        });

        return response()->json([
            'success' => true,
            'message' => "Password reset. A new temporary password has been emailed to {$user->email}.",
        ]);
    }

    // UserManagementController.php
public function airlines(Request $request)
{
    $admin = $request->user();

    if ($admin->isSuperAdmin()) {
        $airlines = \App\Models\Airline::select('id', 'name')->orderBy('name')->get();
    } else {
        $airlines = \App\Models\Airline::where('id', $admin->airline_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    return response()->json([
        'success' => true,
        'data' => $airlines,
    ]);
}
    /**
     * Read-only audit feed: refund status changes and ticket amount
     * adjustments, optionally filtered by the user who made them.
     */
    public function auditLog(Request $request)
    {
        $admin = $request->user();

        abort_unless(
            $admin->isSuperAdmin() || $admin->hasRole(UserRole::REFUND_OFFICER),
            403
        );

        $userId = $request->get('user_id');

        $statusLogs = \App\Models\RefundStatusLog::with('user', 'refund')
            ->when($userId, fn($q) => $q->where('changed_by', $userId))
            ->latest()
            ->limit(50)
            ->get();

        $amountLogs = \App\Models\RefundTicketAmountLog::with('user', 'ticket.refund')
            ->when($userId, fn($q) => $q->where('changed_by', $userId))
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'status_changes' => $statusLogs,
            'amount_changes' => $amountLogs,
        ]);
    }
public function store(Request $request)
{
    $admin = $request->user();

    abort_unless($admin->can('create', User::class), 403);

    // Temporary debug: log incoming payload to help diagnose missing airline_id
    Log::info('Admin create-user payload', $request->all());

    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users,email'],
        'password' => ['required', Password::min(8)],
        'role' => ['required', new Enum(UserRole::class)],
        'department' => ['nullable', new Enum(Department::class)],
        'airline_id' => ['required', 'exists:airlines,id'],
    ]);

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'role' => $data['role'],
        'department' => $data['department'] ?? null,
        'airline_id' => $data['airline_id'],
        'active' => true,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'User created.',
        'user' => $user,
    ], 201);
}
}