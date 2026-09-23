<?php

namespace Tests\Feature\Concerns;

use App\Enums\Department;
use App\Enums\RefundStatus;
use App\Enums\UserRole;
use App\Models\Airline;
use App\Models\Refund;
use App\Models\RefundTicket;
use App\Models\User;

trait CreatesRefundTestData
{
    protected function makeAirline(array $overrides = []): Airline
    {
        return Airline::factory()->create($overrides);
    }

    protected function makeUser(UserRole $role, Airline $airline, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role->value,
            'airline_id' => $airline->id,
            'active' => true,
        ], $overrides));
    }

    protected function makeSuperAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::SUPER_ADMIN->value,
            'active' => true,
        ], $overrides));
    }

    protected function makeRefund(Airline $airline, array $overrides = []): Refund
    {
        return Refund::factory()->create(array_merge([
            'airline_id' => $airline->id,
            'current_status' => RefundStatus::NEW_REQUEST->value,
            'current_department' => Department::REFUND->value,
        ], $overrides));
    }

    protected function makeTicket(Refund $refund, array $overrides = []): RefundTicket
    {
        return RefundTicket::factory()->create(array_merge([
            'refund_id' => $refund->id,
        ], $overrides));
    }

    /**
     * Builds a valid tickets[] payload for the calculation endpoints,
     * matching SubmitTicketCalculationRequest's validation rules exactly.
     */
    protected function calculationPayloadFor(RefundTicket $ticket, array $overrides = []): array
    {
        return array_merge([
            'ticket_id' => $ticket->id,
            'fare_paid' => 100000,
            'nuc' => 5000,
            'government_tax_ng' => 3000,
            'security_tax_yq' => 2000,
            'airport_tax_qt' => 1500,
            'insurance' => 1000,
            'is_no_show' => false,
            'no_show_fee' => 0,
        ], $overrides);
    }
}