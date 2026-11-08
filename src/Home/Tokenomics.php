<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The second phase. The founder answers what the token does for the people who
 * hold it, and the figures have to add up before a paper can be written.
 */
final class Tokenomics
{
    /** @var array<string, Allocation> */
    private array $allocations = [];
    private array $use = [];

    public function __construct(
        public readonly int $totalSupply,
        public readonly int $holdMonths = 0,
        public readonly int $cliffMonths = 0,
    ) {
        if ($totalSupply <= 0) {
            throw new \InvalidArgumentException('tokenomics needs a total supply');
        }
        if ($cliffMonths > $holdMonths) {
            throw new \InvalidArgumentException('a cliff runs longer than its hold');
        }
    }

    public function allocate(Allocation $allocation): void
    {
        if (isset($this->allocations[$allocation->bucket])) {
            throw new \InvalidArgumentException('duplicate bucket: ' . $allocation->bucket);
        }
        if ($this->allocated() + $allocation->tokens > $this->totalSupply) {
            throw new \InvalidArgumentException('allocation passes the total supply');
        }
        $this->allocations[$allocation->bucket] = $allocation;
    }

    public function useText(string $key): ?string
    {
        return $this->use[$key] ?? null;
    }

    public function use(string $key, string $text): void
    {
        if (!in_array($key, self::USE, true)) {
            throw new \InvalidArgumentException('unknown use: ' . $key);
        }
        if (trim($text) === '') {
            throw new \InvalidArgumentException('empty use: ' . $key);
        }
        $this->use[$key] = $text;
    }

    public function allocated(): int
    {
        $sum = 0;
        foreach ($this->allocations as $allocation) {
            $sum += $allocation->tokens;
        }

        return $sum;
    }

    public function unallocated(): int
    {
        return $this->totalSupply - $this->allocated();
    }

    public function complete(): bool
    {
        return $this->unallocated() === 0 && count($this->use) === count(self::USE);
    }

    /** @return list<Allocation> */
    public function allocations(): array
    {
        return array_values($this->allocations);
    }

    public function share(Allocation $allocation): int
    {
        return (int) round($allocation->tokens * 10000 / $this->totalSupply);
    }

    public const USE = ['utility', 'demand', 'emission', 'treasury'];
}
