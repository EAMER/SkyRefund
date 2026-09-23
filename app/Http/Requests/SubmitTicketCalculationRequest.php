<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTicketCalculationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Actual authorization is handled in the controller via
        // authorizeAction()/Policy, matching the rest of RefundActionController.
        return true;
    }

    public function rules(): array
    {
        return [
            'tickets' => ['required', 'array', 'min:1'],
            'tickets.*.ticket_id' => ['required', 'integer', 'exists:refund_tickets,id'],
            'tickets.*.fare_paid' => ['required', 'numeric', 'min:0'],
            'tickets.*.nuc' => ['required', 'numeric', 'min:0'],
            'tickets.*.government_tax_ng' => ['required', 'numeric', 'min:0'],
            'tickets.*.security_tax_yq' => ['required', 'numeric', 'min:0'],
            'tickets.*.airport_tax_qt' => ['required', 'numeric', 'min:0'],
            'tickets.*.insurance' => ['required', 'numeric', 'min:0'],
            'tickets.*.is_no_show' => ['required', 'boolean'],
            'tickets.*.no_show_fee' => ['required', 'numeric', 'min:0'],

            // Present only on the "Submit to Commercial" action, not "Save Draft".
            'note' => ['nullable', 'string'],
        ];
    }
}