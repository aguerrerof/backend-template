<?php

namespace App\Http\Requests;

use App\Models\Domain\VariantIds;

class GetProductsVariantsInformationRequest extends BaseFormRequest
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
            'ids' => 'required|array',
            'ids.*' => 'required|string|distinct',
        ];
    }

    public function getVariantsIds(): VariantIds
    {
        $validatedData = $this->validated();
        $ids = $validatedData['ids'];
        return new VariantIds(... $ids);
    }
}
