<?php

namespace App\Http\Requests;

use Carbon\Carbon;

class RecurringChargesRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'charge_date' => 'date_format:Y-m-d',
        ];
    }

    public function getChargeDate(): Carbon
    {
        $chargeDate = $this->input('charge_date');
        if (!$chargeDate) {

            return Carbon::today();
        }
        return Carbon::createFromFormat('Y-m-d', $chargeDate);
    }
}
