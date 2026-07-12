<?php

namespace App\Http\Controllers\Passenger;

use App\Enums\AttachmentType;
use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Services\AttachmentService;
use App\Services\RefundNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortalController extends Controller
{
    public function __construct(
        protected AttachmentService $attachmentService,
        protected RefundNotificationService $notificationService
    ) {
    }

    public function show(Request $request, ?string $reference = null)
    {
        $reference = $reference ?? $request->query('reference');

        if (! $reference) {
            return view('passenger.lookup');
        }

        $refund = Refund::query()
            ->where('reference', $reference)
            ->with(['airline', 'tickets', 'attachments', 'statusLogs.user'])
            ->first();

        if (! $refund) {
            return view('passenger.lookup', ['error' => 'No refund was found for that reference.']);
        }

        return view('passenger.portal', compact('refund'));
    }

    public function upload(Request $request, Refund $refund)
    {
        $request->validate([
            'attachments' => ['required', 'array'],
            'attachments.*' => ['file', 'max:20480'],
            'attachment_types' => ['required', 'array'],
            'attachment_types.*' => ['string', Rule::in(array_map(fn ($value) => $value->value, AttachmentType::cases()))],
        ]);

        $files = $request->file('attachments', []);
        $types = $request->input('attachment_types', []);

        if (is_string($types)) {
            $types = [$types];
        }

        if (count($files) > 1 && count($types) === 1) {
            $types = array_fill(0, count($files), $types[0]);
        }

        $request->merge(['attachment_types' => array_values($types)]);

        $this->attachmentService->store($refund, $request);
        $this->notificationService->sendStatusNotification($refund, 'updated');

        return back()->with('status', 'Additional documents were uploaded successfully.');
    }

    public function receipt(Refund $refund)
    {
        $airlineName = $refund->airline?->name ?? 'SkyRefund';
        $statusLabel = str_replace('_', ' ', $refund->current_status);
        $body = "SkyRefund Confirmation\n";
        $body .= "====================\n";
        $body .= "Reference: {$refund->reference}\n";
        $body .= "Airline: {$airlineName}\n";
        $body .= "Passenger: {$refund->first_name} {$refund->last_name}\n";
        $body .= "Email: {$refund->email}\n";
        $body .= "Status: {$statusLabel}\n";
        $body .= "Department: {$refund->current_department}\n";
        $body .= "Submitted: {$refund->created_at->format('d M Y H:i')}\n\n";
        $body .= "Thank you for using SkyRefund.\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="receipt-' . $refund->reference . '.txt"',
        ]);
    }
}
