<?php

namespace App\Http\Requests;

use App\Enums\AttachmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRefundRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Uncomment only when debugging
        /*
        dd([
            'all' => $this->all(),
            'files' => $this->allFiles(),
            'content_type' => $this->header('Content-Type'),
        ]);
        */

        // Convert checkbox value to boolean
        if ($this->has('consent')) {
            $this->merge([
                'consent' => filter_var($this->input('consent'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Passenger Information
            |--------------------------------------------------------------------------
            */

            'airline_id' => ['required', 'exists:airlines,id'],

            'first_name' => ['required', 'string', 'max:255'],

            'last_name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'email'],

            'phone' => ['required', 'string', 'max:30'],

            'address' => ['nullable', 'string'],

            /*
            |--------------------------------------------------------------------------
            | Refund
            |--------------------------------------------------------------------------
            */

            'refund_reason' => ['required', 'string'],

            'refund_type' => ['required', 'string'],

            'passenger_explanation' => ['required', 'string'],

            /*
            |--------------------------------------------------------------------------
            | Bank
            |--------------------------------------------------------------------------
            */

            'bank_name' => ['required', 'string'],

            'account_name' => ['required', 'string'],

            'account_number' => ['required', 'string', 'max:30'],

            'account_type' => ['required', 'string'],

            /*
            |--------------------------------------------------------------------------
            | Consent
            |--------------------------------------------------------------------------
            */

            'consent' => ['required', 'boolean'],

            /*
            |--------------------------------------------------------------------------
            | Tickets
            |--------------------------------------------------------------------------
            */

            'tickets' => ['required', 'array', 'min:1'],

            'tickets.*.booking_reference' => ['required', 'string'],

            'tickets.*.ticket_number' => ['required', 'string'],

            'tickets.*.passenger_name' => ['required', 'string'],

            'tickets.*.flight_number' => ['required', 'string'],

            'tickets.*.airline_code' => ['required', 'string'],

            'tickets.*.origin_airport' => ['required', 'string'],

            'tickets.*.destination_airport' => ['required', 'string'],

            'tickets.*.departure_datetime' => ['required', 'date'],

            /*
            |--------------------------------------------------------------------------
            | Attachments
            |--------------------------------------------------------------------------
            */

            'attachments' => ['nullable', 'array'],

            'attachments.*' => [
                'nullable',
                'file',
                'max:10240', // 10MB
            ],

            'attachment_types' => ['nullable', 'array'],

            'attachment_types.*' => [
                'required_with:attachments',
                new Enum(AttachmentType::class),
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'attachments.array' => 'Attachments must be an array.',

            'attachments.*.file' => 'Each attachment must be a valid file.',

            'attachments.*.max' => 'Each attachment must not exceed 10MB.',

            'attachment_types.array' => 'Attachment types must be an array.',

            'attachment_types.*.required_with' =>
                'Each attachment must have a corresponding attachment type.',

            'consent.required' => 'You must accept the consent.',
        ];
    }
}