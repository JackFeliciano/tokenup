<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The builder phase. A draft is what the founder answers before the token exists.
 * Answers arrive in any order and may be corrected until the project is checked.
 */
final class Draft
{
    /** @var array<string, mixed> */
    private array $answers = [];

    public function __construct(public readonly string $handle)
    {
        if (trim($handle) === '') {
            throw new \InvalidArgumentException('a draft carries a handle');
        }
    }

    public function set(string $question, mixed $value): void
    {
        if (!in_array($question, self::QUESTIONS, true)) {
            throw new \InvalidArgumentException('unknown question: ' . $question);
        }
        $this->answers[$question] = $value;
    }

    public function answer(string $question): mixed
    {
        return $this->answers[$question] ?? null;
    }

    public function answered(string $question): bool
    {
        return isset($this->answers[$question]);
    }

    /** @return list<string> */
    public function missing(): array
    {
        return array_values(array_filter(self::QUESTIONS, fn (string $q): bool => !$this->answered($q)));
    }

    public const QUESTIONS = [
        'name', 'symbol', 'network', 'decimals', 'initialSupply', 'maximumSupply',
        'permit', 'mintable', 'burnable', 'pausable', 'unlimitedSupply', 'initialLiquidity',
    ];
}
