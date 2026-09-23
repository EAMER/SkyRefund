<?php

namespace Tests\Feature;

use App\Enums\Department;
use App\Enums\RefundStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRefundTestData;
use Tests\TestCase;

class RefundPolicyGatingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRefundTestData;


    public function test_audit_cannot_approve_a_refund_currently_with_commercial(): void
    {
        $airline = $this->makeAirline();
        $audit = $this->makeUser(UserRole::AUDIT, $airline);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $response = $this->actingAs($audit, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/approve");

        $response->assertStatus(403);
    }

    public function test_user_from_a_different_airline_cannot_view_the_refund(): void
    {
        $airlineA = $this->makeAirline();
        $airlineB = $this->makeAirline();

        $commercialFromB = $this->makeUser(UserRole::COMMERCIAL, $airlineB);

        $refund = $this->makeRefund($airlineA, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $response = $this->actingAs($commercialFromB, 'sanctum')
            ->getJson("/api/v1/admin/refunds/{$refund->id}");

        $response->assertStatus(404);
    }

    public function test_super_admin_can_act_regardless_of_department(): void
    {
        $airline = $this->makeAirline();
        $superAdmin = $this->makeSuperAdmin(['airline_id' => $airline->id]);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_AUDIT->value,
            'current_department' => Department::AUDIT->value,
        ]);

        $response = $this->actingAs($superAdmin, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/approve");

        $response->assertOk();
    }

    /**
     * updateDepartment() is a manual override that bypasses the normal
     * status-driven workflow, so — unlike approve()/reject()/etc — it's
     * intentionally NOT gated to whichever department currently owns the
     * refund. Only SUPER_ADMIN may use it.
     */
    public function test_department_reassignment_is_super_admin_only(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $superAdmin = $this->makeSuperAdmin(['airline_id' => $airline->id]);

        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/department", [
                'department' => Department::AUDIT->value,
            ])
            ->assertStatus(403);

        $this->actingAs($superAdmin, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/department", [
                'department' => Department::AUDIT->value,
            ])
            ->assertOk();

        $refund->refresh();
        $this->assertSame(Department::AUDIT, $refund->current_department);
        // Reassignment is a manual override, not a workflow transition —
        // current_status must stay untouched.
        $this->assertSame(RefundStatus::PENDING_COMMERCIAL, $refund->current_status);
    }

    public function test_department_reassignment_logs_to_the_activity_feed(): void
    {
        $airline = $this->makeAirline();
        $superAdmin = $this->makeSuperAdmin(['airline_id' => $airline->id]);
        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $this->actingAs($superAdmin, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/department", [
                'department' => Department::FINANCE->value,
                'reason' => 'Escalated by ops lead',
            ])
            ->assertOk();

        $this->assertDatabaseHas('refund_status_logs', [
            'refund_id' => $refund->id,
        ]);

        $log = $refund->statusLogs()->latest()->first();
        $this->assertStringContainsString('Commercial', $log->note);
        $this->assertStringContainsString('Finance', $log->note);
        $this->assertStringContainsString('Escalated by ops lead', $log->note);
    }

    public function test_only_officer_or_super_admin_can_upload_attachments(): void
    {
        $airline = $this->makeAirline();
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);
        $officer = $this->makeUser(UserRole::REFUND_OFFICER, $airline);

        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_COMMERCIAL->value,
            'current_department' => Department::COMMERCIAL->value,
        ]);

        $this->actingAs($commercial, 'sanctum')
            ->postJson("/api/v1/admin/refunds/{$refund->id}/attachments", [])
            ->assertStatus(403);

        // Officer is authorized (though this specific call will fail
        // validation with no file attached — 422, not 403 — which is
        // enough to confirm the authorization gate itself passed).
        $response = $this->actingAs($officer, 'sanctum')
            ->postJson("/api/v1/admin/refunds/{$refund->id}/attachments", []);

        $response->assertStatus(422);
    }
}