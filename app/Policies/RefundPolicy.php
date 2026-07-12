<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Refund;
use App\Models\User;

class RefundPolicy
{
    public function assign(User $user, Refund $refund): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN) || $user->hasRole(UserRole::REFUND_OFFICER);
    }

    public function approve(User $user, Refund $refund): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN)
            || $user->hasRole(UserRole::COMMERCIAL)
            || $user->hasRole(UserRole::AUDIT)
            || $user->hasRole(UserRole::FINANCE)
            || $user->hasRole(UserRole::TREASURY);
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
        return $user->hasRole(UserRole::SUPER_ADMIN)
            || $user->hasRole(UserRole::REFUND_OFFICER);
    }

    public function complete(User $user, Refund $refund): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN)
            || $user->hasRole(UserRole::TREASURY);
    }

    public function updatePriority(User $user, Refund $refund): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN)
            || $user->hasRole(UserRole::REFUND_OFFICER);
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
