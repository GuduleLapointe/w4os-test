<?php
declare(strict_types=1);

use CodeNameGenerator\CodeNameGenerator;
use CodeNameGenerator\Locales\English;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CodeNameGenerator\CodeNameGenerator
 */
final class NamegenTest extends TestCase
{
    /**
     * @covers ::generate
     * @covers ::__construct
     * @covers \CodeNameGenerator\Locales\English
     */
    public function testDefaultsGeneratesTwoEnglishWords(): void
    {
        $generator = new CodeNameGenerator();
        $name = $generator->generate();
        $words = explode('-', $name);
        $locale = new English();

        $this->assertCount(2, $words);
        $this->assertContains($words[0], $locale->adjectives());
        $this->assertContains($words[1], $locale->nouns());
    }
}
