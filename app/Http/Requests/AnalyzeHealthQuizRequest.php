<?php

namespace App\Http\Requests;

use App\Models\Domain\QuizPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyzeHealthQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'species_id' => ['required', 'integer', 'exists:species,id'],
            'breed_id' => [
                'nullable',
                'integer',
                Rule::exists('breeds', 'id')->where(
                    fn ($query) => $query->where('species_id', $this->input('species_id'))
                ),
            ],
            'breed' => ['nullable', 'string', 'max:120'],
            'sex' => ['required', 'in:male,female'],
            'is_neutered' => ['required', 'boolean'],
            'symptoms' => ['nullable', 'array', 'required_without:extra_symptoms'],
            'symptoms.*' => ['required','integer', 'exists:symptoms,id'],
            'medical_conditions' => ['nullable', 'array'],
            'medical_conditions.*' => ['integer', 'exists:medical_conditions,id'],
            'extra_symptoms' => ['nullable', 'string', 'max:1000', 'required_without:symptoms'],
            'age' => ['required', 'integer', 'min:0'],
            'age_unit' => ['required', 'in:months,years'],
            'weight' => ['nullable', 'numeric', 'min:0.1'],
            'weight_unit' => ['nullable', 'in:kg,g'],
        ];
    }

    public function messages(): array
    {
        return [
            'species_id.required' => 'La especie es obligatoria.',
            'symptoms.min' => 'Debe seleccionar al menos un sintoma.',
            'symptoms.required_without' => 'Debe enviar sintomas o descripcion adicional.',
            'extra_symptoms.required_without' => 'Si no envia sintomas, la descripcion adicional es obligatoria.',
        ];
    }

    public function getQuizPayload(): QuizPayload
    {
        return new QuizPayload(
            $this->get('species_id'),
            $this->get('breed_id'),
            $this->filled('breed') ? $this->get('breed') : null,
            $this->get('sex'),
            $this->get('is_neutered'),
            $this->input('symptoms', []),
            $this->get('medical_conditions'),
            $this->get('age'),
            $this->get('weight'),
            $this->get('age_unit'),
            $this->get('weight_unit'),
            $this->filled('extra_symptoms') ? $this->get('extra_symptoms') : null,
        );
    }
}
