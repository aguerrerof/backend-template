<?php

namespace App\Services\Diagnostic;

use App\Models\Breed;
use Illuminate\Support\Facades\Cache;

class BreedTextResolver
{
    private const DEFAULT_BREED = 'Mestizo / Sin raza';

    /**
     * @return array{
     *   input: string,
     *   breed_id: int|null,
     *   breed_name: string,
     *   confidence: float|null,
     *   matched: bool
     * }
     */
    public function resolve(int $speciesId, string $input): array
    {
        $sanitizedInput = $this->sanitize($input);
        $normalizedInput = $this->normalizeComparable($sanitizedInput);

        if ($normalizedInput === '') {
            return [
                'input' => '',
                'breed_id' => null,
                'breed_name' => self::DEFAULT_BREED,
                'confidence' => null,
                'matched' => false,
            ];
        }

        if ($this->isMixedBreed($normalizedInput)) {
            return [
                'input' => $sanitizedInput,
                'breed_id' => null,
                'breed_name' => self::DEFAULT_BREED,
                'confidence' => 1.0,
                'matched' => true,
            ];
        }

        $candidates = $this->getCandidates($speciesId);
        [$best, $bestScore, $matched] = $this->pickBestCandidate($normalizedInput, $candidates);

        return [
            'input' => $sanitizedInput,
            'breed_id' => $matched ? $best['id'] : null,
            'breed_name' => $matched ? $best['name'] : $sanitizedInput,
            'confidence' => $matched ? round($bestScore, 3) : null,
            'matched' => $matched,
        ];
    }

    /**
     * Útil para tests o para integrar catálogos externos sin depender de DB.
     *
     * @param array<int, array{id:int, name:string, is_other?:bool}> $candidates
     * @return array{
     *   input: string,
     *   breed_id: int|null,
     *   breed_name: string,
     *   confidence: float|null,
     *   matched: bool
     * }
     */
    public function resolveFromCandidates(string $input, array $candidates): array
    {
        $sanitizedInput = $this->sanitize($input);
        $normalizedInput = $this->normalizeComparable($sanitizedInput);

        if ($normalizedInput === '') {
            return [
                'input' => '',
                'breed_id' => null,
                'breed_name' => self::DEFAULT_BREED,
                'confidence' => null,
                'matched' => false,
            ];
        }

        if ($this->isMixedBreed($normalizedInput)) {
            return [
                'input' => $sanitizedInput,
                'breed_id' => null,
                'breed_name' => self::DEFAULT_BREED,
                'confidence' => 1.0,
                'matched' => true,
            ];
        }

        $normalizedCandidates = array_map(function (array $candidate) {
            $name = (string) ($candidate['name'] ?? '');

            return [
                'id' => (int) ($candidate['id'] ?? 0),
                'name' => $name,
                'norm' => $this->normalizeComparable($name),
                'is_other' => (bool) ($candidate['is_other'] ?? false),
            ];
        }, $candidates);

        [$best, $bestScore, $matched] = $this->pickBestCandidate($normalizedInput, $normalizedCandidates);

        return [
            'input' => $sanitizedInput,
            'breed_id' => $matched ? $best['id'] : null,
            'breed_name' => $matched ? $best['name'] : $sanitizedInput,
            'confidence' => $matched ? round($bestScore, 3) : null,
            'matched' => $matched,
        ];
    }

    /**
     * @return array<int, array{id:int, name:string, norm:string, is_other:bool}>
     */
    private function getCandidates(int $speciesId): array
    {
        $cacheKey = "species:{$speciesId}:breeds:v1";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($speciesId) {
            return Breed::where('species_id', $speciesId)
                ->get(['id', 'name', 'is_other'])
                ->map(function (Breed $breed) {
                    return [
                        'id' => $breed->id,
                        'name' => $breed->name,
                        'norm' => $this->normalizeComparable($breed->name),
                        'is_other' => (bool) $breed->is_other,
                    ];
                })
                ->all();
        });
    }

    /**
     * @param array<int, array{id:int, name:string, norm:string, is_other:bool}> $candidates
     * @return array{0: array{id:int, name:string, norm:string, is_other:bool}|null, 1: float, 2: bool}
     */
    private function pickBestCandidate(string $normalizedInput, array $candidates): array
    {
        $best = null;
        $bestScore = 0.0;

        foreach ($candidates as $candidate) {
            if ($candidate['norm'] === '') {
                continue;
            }

            $score = $this->similarity($normalizedInput, $candidate['norm']);
            if ($candidate['is_other']) {
                $score *= 0.8;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        $matched = $best !== null && $this->passesThreshold($normalizedInput, $best['norm'], $bestScore);

        return [$best, $bestScore, $matched];
    }

    private function sanitize(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/[\r\n]+/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return mb_substr(trim($text), 0, 120);
    }

    private function normalizeComparable(string $text): string
    {
        $text = mb_strtolower($text);

        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($ascii !== false) {
            $text = $ascii;
        }

        $text = preg_replace('/[^a-z0-9]+/i', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function isMixedBreed(string $normalizedInput): bool
    {
        $patterns = [
            'mestizo',
            'sin raza',
            'criollo',
            'mix',
            'mixed',
            'mutt',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($normalizedInput, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function similarity(string $a, string $b): float
    {
        if ($a === $b) {
            return 1.0;
        }

        if (str_contains($a, $b) || str_contains($b, $a)) {
            $short = min(strlen($a), strlen($b));
            $long = max(strlen($a), strlen($b));

            if ($short >= 4) {
                return min(1.0, ($short / $long) + 0.15);
            }
        }

        $maxLen = max(strlen($a), strlen($b));
        if ($maxLen === 0) {
            return 0.0;
        }

        $distance = levenshtein($a, $b);
        $ratio = 1.0 - ($distance / $maxLen);

        return max(0.0, min(1.0, $ratio));
    }

    private function passesThreshold(string $inputNorm, string $candidateNorm, float $score): bool
    {
        $len = strlen($inputNorm);

        if ($len <= 3) {
            return $inputNorm === $candidateNorm;
        }

        if ($len <= 5) {
            return $score >= 0.86;
        }

        return $score >= 0.8;
    }
}
