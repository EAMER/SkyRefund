<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateTicketAmountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled via policy in the controller
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'min:5'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {

            $ticket = $this->route('ticket');

            if (! $ticket) {
                return;
            }

            $amount = (float) $this->input('amount');

            if ($amount > (float) $ticket->fare_paid) {

                $validator->errors()->add(
                    'amount',
                    'Refund amount cannot exceed the fare paid ('
                        . number_format((float) $ticket->fare_paid, 2)
                        . ' ' . $ticket->currency . ').'
                );
            }

        });
    }
}