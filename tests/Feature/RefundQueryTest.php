<?php

namespace Tests\Feature;

use App\Enums\Department;
use App\Enums\RefundStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRefundTestData;
use Tests\TestCase;

class RefundQueryTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRefundTestData;


    public function test_any_department_can_raise_a_query_directed_at_another_user(): void
    {
        $airline = $this->makeAirline();
        $audit = $this->makeUser(UserRole::AUDIT, $airline);
        $finance = $this->makeUser(UserRole::FINANCE, $airline);

        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_AUDIT->value,
            'current_department' => Department::AUDIT->value,
        ]);

        $response = $this->actingAs($audit, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/queries",
            [
                'directed_to_user_id' => $finance->id,
                'message' => 'Can you confirm the payment method on file?',
            ]
        );

        $response->assertCreated();
        $this->assertDatabaseHas('refund_queries', [
            'refund_id' => $refund->id,
            'raised_by_user_id' => $audit->id,
            'directed_to_user_id' => $finance->id,
        ]);
    }

    public function test_only_the_directed_to_user_can_resolve_a_query(): void
    {
        $airline = $this->makeAirline();
        $audit = $this->makeUser(UserRole::AUDIT, $airline);
        $finance = $this->makeUser(UserRole::FINANCE, $airline);
        $commercial = $this->makeUser(UserRole::COMMERCIAL, $airline);

        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_AUDIT->value,
            'current_department' => Department::AUDIT->value,
        ]);

        $create = $this->actingAs($audit, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/queries",
            ['directed_to_user_id' => $finance->id, 'message' => 'Please confirm.']
        );
        $queryId = $create->json('query.id');

        // Someone else (not the directed-to user) can't resolve it.
        $this->actingAs($commercial, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/queries/{$queryId}/resolve", [
                'response' => 'Confirmed.',
            ])
            ->assertStatus(403);

        // The actual directed-to user can.
        $this->actingAs($finance, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/queries/{$queryId}/resolve", [
                'response' => 'Confirmed.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('refund_queries', [
            'id' => $queryId,
            'response' => 'Confirmed.',
            'resolved_by_user_id' => $finance->id,
        ]);
    }

    public function test_resolving_an_already_resolved_query_is_rejected(): void
    {
        $airline = $this->makeAirline();
        $audit = $this->makeUser(UserRole::AUDIT, $airline);
        $finance = $this->makeUser(UserRole::FINANCE, $airline);

        $refund = $this->makeRefund($airline, [
            'current_status' => RefundStatus::PENDING_AUDIT->value,
            'current_department' => Department::AUDIT->value,
        ]);

        $create = $this->actingAs($audit, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/queries",
            ['directed_to_user_id' => $finance->id, 'message' => 'Please confirm.']
        );
        $queryId = $create->json('query.id');

        $this->actingAs($finance, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/queries/{$queryId}/resolve", [
                'response' => 'Confirmed.',
            ])
            ->assertOk();

        $response = $this->actingAs($finance, 'sanctum')
            ->patchJson("/api/v1/admin/refunds/{$refund->id}/queries/{$queryId}/resolve", [
                'response' => 'Confirmed again.',
            ]);

        $response->assertStatus(422);
    }

    public function test_query_cannot_be_directed_to_a_user_in_a_different_airline(): void
    {
        $airlineA = $this->makeAirline();
        $airlineB = $this->makeAirline();

        $audit = $this->makeUser(UserRole::AUDIT, $airlineA);
        $outsideUser = $this->makeUser(UserRole::FINANCE, $airlineB);

        $refund = $this->makeRefund($airlineA, [
            'current_status' => RefundStatus::PENDING_AUDIT->value,
            'current_department' => Department::AUDIT->value,
        ]);

        $response = $this->actingAs($audit, 'sanctum')->postJson(
            "/api/v1/admin/refunds/{$refund->id}/queries",
            ['directed_to_user_id' => $outsideUser->id, 'message' => 'Please confirm.']
        );

        $response->assertStatus(403);
    }
}