<?php

namespace App\Services;

use App\Models\Refund;
use Carbon\Carbon;

class RefundAiService
{
    public function analyze(Refund $refund): array
    {
        $flags = [];
        $score = 0.0;

        if ($this->looksLikeDuplicate($refund)) {
            $flags[] = 'duplicate_refund';
            $score += 40;
        }

        if ($this->hasMissingDocuments($refund)) {
            $flags[] = 'missing_documents';
            $score += 25;
        }

        if ($this->looksSuspicious($refund)) {
            $flags[] = 'suspicious_pattern';
            $score += 20;
        }

        if ($refund->passenger_explanation && strlen(trim($refund->passenger_explanation)) < 20) {
            $flags[] = 'short_explanation';
            $score += 10;
        }

        $flagged = $score >= 40;

        $suggestedPriority = match (true) {
            $score >= 70 => 'HIGH',
            $score >= 40 => 'MEDIUM',
            default => 'LOW',
        };

        $suggestedDepartment = match (true) {
            $score >= 70 => 'AUDIT',
            $score >= 40 => 'COMMERCIAL',
            default => 'REFUND',
        };

        return [
            'ai_flagged' => $flagged,
            'ai_score' => round(min($score, 100), 2),
            'suggested_priority' => $suggestedPriority,
            'suggested_department' => $suggestedDepartment,
            'flags' => $flags,
            'summary' => $this->buildSummary($flagged, $flags, $score),
        ];
    }

    public function apply(Refund $refund): Refund
    {
        $result = $this->analyze($refund);

        $refund->forceFill([
            'ai_flagged' => $result['ai_flagged'],
            'ai_score' => $result['ai_score'],
            'ai_processed_at' => Carbon::now(),
            'priority' => $result['suggested_priority'],
            'current_department' => $result['suggested_department'],
        ])->save();

        $refund->analyses()->create([
            'summary' => $result['summary'],
            'score' => $result['ai_score'],
            'flagged' => $result['ai_flagged'],
            'details' => json_encode($result['flags']),
        ]);

        return $refund->fresh();
    }

    protected function looksLikeDuplicate(Refund $refund): bool
    {
        return Refund::where('email', $refund->email)
            ->where('id', '!=', $refund->id)
            ->exists();
    }

    protected function hasMissingDocuments(Refund $refund): bool
    {
        return $refund->attachments()->count() < 1;
    }

    protected function looksSuspicious(Refund $refund): bool
    {
        return str_contains(strtolower($refund->address ?? ''), 'unknown')
            || str_contains(strtolower($refund->bank_name ?? ''), 'test');
    }

    protected function buildSummary(bool $flagged, array $flags, float $score): string
    {
        $summary = 'AI review completed.';

        if ($flagged) {
            $summary .= ' High-risk indicators detected: ' . implode(', ', $flags) . '.';
        } else {
            $summary .= ' No high-risk indicators detected.';
        }

        return $summary . ' Score: ' . round($score, 2);
    }
}
