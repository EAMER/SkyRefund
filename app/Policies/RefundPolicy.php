<?php

namespace App\Policies;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Models\Refund;
use App\Models\User;

class RefundPolicy
{
    /**
     * Verify user belongs to refund airline tenant.
     * Super admins bypass tenant scoping entirely.
     */
    private function sameTenant(User $user, Refund $refund): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->airline_id === $refund->airline_id;
    }


    public function assign(User $user, Refund $refund): bool
    {
        return $this->sameTenant($user, $refund)
            && (
                $user->hasRole(UserRole::SUPER_ADMIN)
                || $user->hasRole(UserRole::REFUND_OFFICER)
            );
    }


    public function approve(User $user, Refund $refund): bool
{
    if (! $this->sameTenant($user, $refund)) {
        return false;
    }

    if ($user->hasRole(UserRole::SUPER_ADMIN)) {
        return true;
    }

    return match ($refund->current_department) {
        Department::REFUND => $user->hasRole(UserRole::REFUND_OFFICER),
        Department::COMMERCIAL => $user->hasRole(UserRole::COMMERCIAL),
        Department::AUDIT => $user->hasRole(UserRole::AUDIT),
        Department::FINANCE => $user->hasRole(UserRole::FINANCE),
        Department::TREASURY => $user->hasRole(UserRole::TREASURY),
        default => false,
    };
}


    public function reject(User $user, Refund $refund): bool
    {
        return $this->approve($user, $refund);
    }


    public function returnBack(User $user, Refund $refund): bool
    {
        return $this->approve($user, $refund);
    }


    public function cancel(User $user, Refund $refund): bool
    {
        return $this->sameTenant($user, $refund)
            && (
                $user->hasRole(UserRole::SUPER_ADMIN)
                || $user->hasRole(UserRole::REFUND_OFFICER)
            );
    }


    public function complete(User $user, Refund $refund): bool
    {
        return $this->sameTenant($user, $refund)
            && (
                $user->hasRole(UserRole::SUPER_ADMIN)
                || $user->hasRole(UserRole::TREASURY)
            );
    }


    public function updatePriority(User $user, Refund $refund): bool
    {
        return $this->sameTenant($user, $refund)
            && (
                $user->hasRole(UserRole::SUPER_ADMIN)
                || $user->hasRole(UserRole::REFUND_OFFICER)
            );
    }

    public function updateTicketAmount(User $user, Refund $refund): bool
{
    return $this->approve($user, $refund);
}

    public function updateDepartment(User $user, Refund $refund): bool
    {
        return $this->approve($user, $refund);
    }


    public function updateNotes(User $user, Refund $refund): bool
    {
        return $this->approve($user, $refund);
    }
}