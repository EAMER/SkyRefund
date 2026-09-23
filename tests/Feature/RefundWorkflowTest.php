<?php

namespace Tests\Feature;

use App\Enums\Department;
use App\Enums\RefundStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRefundTestData;
use Tests\TestCase;

class RefundWorkflowTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRefundTestData;


    /*
    |--------------------------------------------------------------------------
    | Calculation — Save Draft / Submit
    |--------------------------------------------------------------------------
    */

    public function test_officer_can_save_calculation_as_draft_without_changing_status(): void
    {
        $airline = $this->makeAirline();
        $officer = $this->makeUser(UserRole::REFUND_OFFICER, $airline);
        $refund = $this->makeRefund($airline);
        $ticket = $this->makeTicket($refund);

        $response = $this->actingAs($officer, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/tickets/calculation/draft",
            ['tickets' => [$this->calculationPayloadFor($ticket)]]
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $refund->refresh();
        $this->assertSame(RefundStatus::NEW_REQUEST, $refund->current_status);

        $ticket->refresh();
        $this->assertEquals(100000, $ticket->fare_paid);
        // 5000 + 3000 + 2000 + 1500 + 1000, no-show fee excluded since is_no_show is false
        $this->assertEquals(12500, $ticket->total_deduction);
        $this->assertEquals(87500, $ticket->refund_amount);
    }

    public function test_officer_submit_moves_refund_to_pending_commercial(): void
    {
        $airline = $this->makeAirline();
        $officer = $this->makeUser(UserRole::REFUND_OFFICER, $airline);
        $refund = $this->makeRefund($airline);
        $ticket = $this->makeTicket($refund);

        $response = $this->actingAs($officer, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/tickets/calculation/submit",
            ['tickets' => [$this->calculationPayloadFor($ticket)]]
        );

        $response->assertOk();

        $refund->refresh();
        $this->assertSame(RefundStatus::PENDING_COMMERCIAL, $refund->current_status);
        $this->assertSame(Department::COMMERCIAL, $refund->current_department);
    }

    public function test_no_show_fee_is_included_in_deduction_only_when_checked(): void
    {
        $airline = $this->makeAirline();
        $officer = $this->makeUser(UserRole::REFUND_OFFICER, $airline);
        $refund = $this->makeRefund($airline);
        $ticket = $this->makeTicket($refund);

        $this->actingAs($officer, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/tickets/calculation/draft",
            ['tickets' => [$this->calculationPayloadFor($ticket, [
                'is_no_show' => true,
                'no_show_fee' => 15000,
            ])]]
        )->assertOk();

        $ticket->refresh();
        $this->assertEquals(27500, $ticket->total_deduction); // 12500 + 15000
        $this->assertEquals(72500, $ticket->refund_amount);
    }

    public function test_calculation_submit_rejects_missing_required_fields(): void
    {
        $airline = $this->makeAirline();
        $officer = $this->makeUser(UserRole::REFUND_OFFICER, $airline);
        $refund = $this->makeRefund($airline);
        $ticket = $this->makeTicket($refund);

        $payload = $this->calculationPayloadFor($ticket);
        unset($payload['fare_paid']);

        $response = $this->actingAs($officer, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/tickets/calculation/submit",
            ['tickets' => [$payload]]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tickets.0.fare_paid']);
    }

    public function test_only_refund_officer_or_super_admin_can_submit_calculation(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $refund = $this->makeRefund($airline);
        $ticket = $this->makeTicket($refund);

        $response = $this->actingAs($commercial, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/tickets/calculation/submit",
            ['tickets' => [$this->calculationPayloadFor($ticket)]]
        );

        $response->assertStatus(403);
    }


    /*
    |--------------------------------------------------------------------------
    | Approve / Return / Resubmit — the core bugs fixed this session
    |--------------------------------------------------------------------------
    */

    public function test_commercial_can_approve_moving_refund_to_pending_audit(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $response = $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/approve", ['note' => 'Looks fine']);

        $response->assertOk();

        $refund->refresh();
        $this->assertSame(RefundStatus::PENDING_AUDIT, $refund->current_status);
        $this->assertSame(Department::AUDIT, $refund->current_department);
    }

    /**
     * The main bug fixed this session: returnBack() used to call
     * RefundStatus::returnedTo(), which only works walking the OTHER
     * direction (RETURNED_BY_* back to the previous stage), so calling
     * Return from an actual PENDING_* status always threw. This test
     * guards against that regression coming back.
     */
    public function test_commercial_can_return_a_pending_refund(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $response = $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/return", ['note' => 'Missing documents']);

        $response->assertOk();

        $refund->refresh();
        $this->assertSame(RefundStatus::RETURNED_BY_COMMERCIAL, $refund->current_status);
    }

    /**
     * The second bug fixed alongside the above: a returned refund used to
     * stay assigned to the RETURNING department instead of routing back
     * to the Refund Officer, so Commercial would still see it in their own
     * queue after returning it, and the officer never would.
     */
    public function test_returned_refund_routes_back_to_refund_department_not_the_returning_department(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/return", ['note' => 'Missing documents'])
            ->assertOk();

        $refund->refresh();
        $this->assertSame(Department::REFUND, $refund->current_department);
    }

    /**
     * Returning also assigns the refund to whoever performed the return,
     * so it's flagged for that specific person once corrected — soft
     * routing, not a hard access restriction.
     */
    public function test_returning_a_refund_assigns_it_to_the_returning_user(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/return", ['note' => 'Missing documents'])
            ->assertOk();

        $refund->refresh();
        $this->assertSame($commercial->id, $refund->assigned_to);
    }

    /**
     * Resubmitting after a correction must return to the SAME department
     * that returned it (Commercial -> Commercial), not restart the whole
     * chain from scratch — this was broken before submitCalculation()
     * was extended to handle RETURNED_BY_* statuses.
     */
    public function test_officer_resubmit_after_return_goes_back_to_the_returning_department(): void
    {
        $airline = $this->makeAirline();
        $officer = $this->makeUser(UserRole::REFUND_OFFICER, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::RETURNED_BY_COMMERCIAL->value,
            'current_department' => Department::REFUND->value,
        ]);
        $ticket = $this->makeTicket($refund);

        $response = $this->actingAs($officer, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/tickets/calculation/submit",
            ['tickets' => [$this->calculationPayloadFor($ticket)]]
        );

        $response->assertOk();

        $refund->refresh();
        $this->assertSame(RefundStatus::PENDING_COMMERCIAL, $refund->current_status);
        $this->assertSame(Department::COMMERCIAL, $refund->current_department);
    }

    public function test_full_chain_from_commercial_to_treasury(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $audit = $this->makeUser(UserRole::AUDIT, $airline);
        $finance = $this->makeUser(UserRole::FINANCE, $airline);

        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/approve")
            ->assertOk();

        $this->actingAs($audit, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/approve")
            ->assertOk();

        $this->actingAs($finance, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/approve")
            ->assertOk();

        $refund->refresh();
        $this->assertSame(RefundStatus::PENDING_TREASURY, $refund->current_status);
        $this->assertSame(Department::TREASURY, $refund->current_department);
    }


    /*
    |--------------------------------------------------------------------------
    | Treasury completion — requires a payment reference
    |--------------------------------------------------------------------------
    */

    public function test_treasury_can_complete_with_a_payment_reference(): void
    {
        $airline = $this->makeAirline();
        $treasury = $this->makeUser(UserRole::TREASURY, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_TREASURY->value,
            'current_department' => Department::TREASURY->value,
        ]);

        $response = $this->actingAs($treasury, 'sanctum')->patchJson(
            "/api/v1/admin/refunds/{$refund->id}/complete",
            ['payment_reference' => 'TXN-12345']
        );

        $response->assertOk();

        $refund->refresh();
        $this->assertSame(RefundStatus::REFUND_COMPLETED, $refund->current_status);
        $this->assertSame('TXN-12345', $refund->payment_reference);
        $this->assertNotNull($refund->paid_at);
    }

    public function test_complete_without_payment_reference_is_rejected(): void
    {
        $airline = $this->makeAirline();
        $treasury = $this->makeUser(UserRole::TREASURY, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_TREASURY->value,
            'current_department' => Department::TREASURY->value,
        ]);

        $response = $this->actingAs($treasury, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/complete", []);

        $response->assertStatus(422);
    }


    /*
    |--------------------------------------------------------------------------
    | Reject / Cancel — terminal states
    |--------------------------------------------------------------------------
    */

    public function test_reject_requires_a_reason_and_ends_the_workflow(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/reject", [])
            ->assertStatus(422);

        $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/reject", ['reason' => 'Fraudulent claim'])
            ->assertOk();

        $refund->refresh();
        $this->assertSame(RefundStatus::REJECTED, $refund->current_status);
    }

    public function test_officer_can_cancel_a_new_request(): void
    {
        $airline = $this->makeAirline();
        $officer = $this->makeUser(UserRole::REFUND_OFFICER, $airline);
        $refund = $this->makeRefund($airline);

        $response = $this->actingAs($officer, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/cancel", ['reason' => 'Duplicate request']);

        $response->assertOk();

        $refund->refresh();
        $this->assertSame(RefundStatus::CANCELLED, $refund->current_status);
    }
}