<?php

namespace Tests\Unit;

use App\Services\Diagnostic\BreedTextResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BreedTextResolverTest extends TestCase
{
    #[Test]
    public function it_resolves_exact_breed_name(): void
    {
        $resolver = app(BreedTextResolver::class);
        $result = $resolver->resolveFromCandidates('Golden Retriever', [
            ['id' => 10, 'name' => 'Golden Retriever'],
            ['id' => 11, 'name' => 'Chihuahua'],
        ]);

        $this->assertTrue($result['matched']);
        $this->assertSame(10, $result['breed_id']);
        $this->assertSame('Golden Retriever', $result['breed_name']);
        $this->assertSame(1.0, $result['confidence']);
    }

    #[Test]
    public function it_resolves_misspelled_breed_name(): void
    {
        $resolver = app(BreedTextResolver::class);
        $result = $resolver->resolveFromCandidates('golden retrever', [
            ['id' => 10, 'name' => 'Golden Retriever'],
            ['id' => 11, 'name' => 'Chihuahua'],
        ]);

        $this->assertTrue($result['matched']);
        $this->assertSame('Golden Retriever', $result['breed_name']);
        $this->assertNotNull($result['breed_id']);
        $this->assertNotNull($result['confidence']);
        $this->assertGreaterThanOrEqual(0.8, $result['confidence']);
    }

    #[Test]
    public function it_maps_mixed_breed_synonyms_to_default(): void
    {
        $resolver = app(BreedTextResolver::class);
        $result = $resolver->resolveFromCandidates('Mestizo', [
            ['id' => 10, 'name' => 'Golden Retriever'],
        ]);

        $this->assertTrue($result['matched']);
        $this->assertNull($result['breed_id']);
        $this->assertSame('Mestizo / Sin raza', $result['breed_name']);
        $this->assertSame(1.0, $result['confidence']);
    }

    #[Test]
    public function it_returns_raw_text_when_no_reasonable_match(): void
    {
        $resolver = app(BreedTextResolver::class);
        $result = $resolver->resolveFromCandidates('Unicornio Galáctico', [
            ['id' => 10, 'name' => 'Golden Retriever'],
            ['id' => 11, 'name' => 'Chihuahua'],
            ['id' => 99, 'name' => 'Otra', 'is_other' => true],
        ]);

        $this->assertFalse($result['matched']);
        $this->assertNull($result['breed_id']);
        $this->assertSame('Unicornio Galáctico', $result['breed_name']);
        $this->assertNull($result['confidence']);
    }
}
