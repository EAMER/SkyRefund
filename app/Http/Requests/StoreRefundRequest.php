<?php

namespace App\Http\Requests;

use App\Enums\AttachmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;


class StoreRefundRequest extends FormRequest
{  
    protected function prepareForValidation(): void
    {
    dd($this->all());
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

            'consent' => ['required' , 'boolean'],

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

            'attachment_types' => ['nullable', 'array'],

                'attachment_types.*' => [
                    'required_with:attachments',
                    'string',
                    'in:signature,passenger_id,account_holder_id,authorization_letter,other',
                    //new Enum(AttachmentType::class),
                ],
        ];
    }
}