<?php

namespace CodeNameGenerator;

use CodeNameGenerator\Interfaces\LocaleInterface;
use CodeNameGenerator\Locales\English;

class CodeNameGenerator
{
    /** @var LocaleInterface */
    private LocaleInterface $locale;

    /**
     * CodeNameGenerator Constructor.
     *
     * @param LocaleInterface|null $locale (Optional, defaults to English)
     */
    public function __construct(?LocaleInterface $locale = null)
    {
        $this->locale = $locale ?? new English();
    }

    /**
     * Generates a single code name.
     *
     * @param int $number_of_adjectives <optional>
     * @return string
     */
    public function generate(int $number_of_adjectives = 1): string
    {
        $adjectives = $this->locale->adjectives();
        $nouns = $this->locale->nouns();

        $result = [];
        for ($i = 0; $i < $number_of_adjectives; ++$i) {
            $result[] = $adjectives[array_rand($adjectives)];
        }
        $result[] = $nouns[array_rand($nouns)];

        return implode('-', $result);
    }
}
