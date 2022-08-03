<?php
declare(strict_types=1);

namespace CodeNameGenerator\Interfaces;

interface LocaleInterface
{
    /**
     * @return string[]
     */
    public function adjectives(): array;

    /**
     * @return string[]
     */
    public function nouns(): array;
}
