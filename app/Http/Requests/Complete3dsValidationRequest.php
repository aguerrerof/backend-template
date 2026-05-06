<?php

namespace App\Http\Requests;

use App\Models\PaymentGateway\DataTransactionThreeDsResource;

class Complete3dsValidationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function getDataTransactionThreeDsResource(): DataTransactionThreeDsResource
    {
        return new DataTransactionThreeDsResource(
            (string)$this->query('pti'),
            (string)$this->query('pcc'),
            (string)$this->query('ptk'),
            (string)$this->query('prc', ''),
        );
    }
}
