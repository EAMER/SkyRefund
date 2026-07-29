<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Enums\AttachmentType;
use App\Enums\RefundReason;
use App\Enums\RefundType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRefundRequest extends FormRequest
{
    /**
     * Prepare request data.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('consent')) {

            $this->merge([
                'consent' => filter_var(
                    $this->input('consent'),
                    FILTER_VALIDATE_BOOLEAN
                ),
            ]);
        }
    }



    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Passenger Information
            |--------------------------------------------------------------------------
            */


            'airline_id' => [
                'required',
                'exists:airlines,id'
            ],


            'first_name' => [
                'required',
                'string',
                'max:255'
            ],


            'last_name' => [
                'required',
                'string',
                'max:255'
            ],


            'email' => [
                'required',
                'email',
                'max:255'
            ],


            'phone' => [
                'required',
                'string',
                'max:30'
            ],


            'address' => [
                'nullable',
                'string'
            ],



            /*
            |--------------------------------------------------------------------------
            | Refund Information
            |--------------------------------------------------------------------------
            */


            'refund_reason' => [
                'required',
                new Enum(RefundReason::class)
            ],


            'refund_type' => [
                'required',
                new Enum(RefundType::class)
            ],


            'passenger_explanation' => [
                'required',
                'string',
                'min:20'
            ],



            /*
            |--------------------------------------------------------------------------
            | Bank Information
            |--------------------------------------------------------------------------
            */


            'bank_name' => [
                'required',
                'string',
                'max:255'
            ],


            'account_name' => [
                'required',
                'string',
                'max:255'
            ],


            'account_number' => [
                'required',
                'string',
                'max:30'
            ],


            'account_type' => [
                'required',
                new Enum(AccountType::class)
            ],



            /*
            |--------------------------------------------------------------------------
            | Consent
            |--------------------------------------------------------------------------
            */


            'consent' => [
                'required',
                'boolean'
            ],



            /*
            |--------------------------------------------------------------------------
            | Tickets
            |--------------------------------------------------------------------------
            */


            'tickets' => [
                'required',
                'array',
                'min:1'
            ],


            'tickets.*.booking_reference' => [
                'required',
                'string',
                'max:100'
            ],


            'tickets.*.ticket_number' => [
                'required',
                'string',
                'max:100'
            ],


            'tickets.*.passenger_name' => [
                'required',
                'string',
                'max:255'
            ],


            'tickets.*.flight_number' => [
                'required',
                'string',
                'max:50'
            ],


            'tickets.*.airline_code' => [
                'required',
                'string',
                'max:10'
            ],


            'tickets.*.origin_airport' => [
                'required',
                'string',
                'max:10'
            ],


            'tickets.*.destination_airport' => [
                'required',
                'string',
                'max:10'
            ],


            'tickets.*.departure_datetime' => [
                'required',
                'date'
            ],



            /*
            |--------------------------------------------------------------------------
            | Attachments
            |--------------------------------------------------------------------------
            */


            'attachments' => [
                'nullable',
                'array'
            ],


            'attachments.*' => [
                'file',
                'max:10240'
            ],


            'attachment_types' => [
                'nullable',
                'array'
            ],


            'attachment_types.*' => [
                'required_with:attachments',
                new Enum(AttachmentType::class)
            ],
        ];
    }



    public function messages(): array
    {
        return [

            'consent.required' =>
                'You must accept the consent.',


            'tickets.required' =>
                'At least one ticket is required.',


            'attachments.*.max' =>
                'Each attachment cannot exceed 10MB.',


            'attachment_types.*.required_with' =>
                'Every attachment must have a type.',

        ];
    }
}