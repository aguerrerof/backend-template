<?php

namespace App\Models\Domain;

readonly class QuizPayload
{
    public function __construct(
        private int     $speciesId,
        private ?int    $breedId,
        private ?string $breedText,
        private string  $sex,
        private bool    $isNeutered,
        private array   $symptomIds,
        private ?array   $medicalConditionIds,
        private int $age,
        private ?float $weight,
        private string $ageUnit,
        private ?string $weightUnit,
        private ?string $extraSymptoms = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'species_id' => $this->speciesId,
            'breed_id' => $this->breedId,
            'breed' => $this->breedText,
            'sex' => $this->sex,
            'is_neutered' => $this->isNeutered,
            'symptom_ids' => $this->symptomIds,
            'medical_condition_ids' => $this->medicalConditionIds,
            'extra_symptoms' => $this->extraSymptoms,
            'weight' => $this->weight,
            'age' => $this->age,
            'weight_unit' => $this->weightUnit,
            'age_unit' => $this->ageUnit,
        ];
    }

    public function toHash(): string
    {
        $data = $this->toArray();
        ksort($data);

        return hash(
            'sha256',
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }

    public function getSpeciesId(): int
    {
        return $this->speciesId;
    }

    public function getBreedId(): ?int
    {
        return $this->breedId;
    }

    public function getBreedText(): ?string
    {
        return $this->breedText;
    }

    public function getSex(): string
    {
        return $this->sex;
    }

    public function isNeutered(): bool
    {
        return $this->isNeutered;
    }

    public function getSymptomIds(): array
    {
        return $this->symptomIds;
    }

    public function getMedicalConditionIds(): ?array
    {
        return $this->medicalConditionIds;
    }

    public function getExtraSymptoms(): ?string
    {
        return $this->extraSymptoms;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function getAgeUnit(): string
    {
        return $this->ageUnit;
    }

    public function getWeightUnit(): ?string
    {
        return $this->weightUnit;
    }
}
