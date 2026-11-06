<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The ledger holds every line in the order it happened. A replay of the lines
 * has to reach the same balance the accounts show, and the demo checks that.
 */
final class Ledger
{
    /** @var list<LedgerLine> */
    private array $lines = [];

    public function record(LedgerLine ...$line): void
    {
        foreach ($line as $one) {
            $this->lines[] = $one;
        }
    }

    /** @return list<LedgerLine> */
    public function lines(): array
    {
        return $this->lines;
    }

    public function count(): int
    {
        return count($this->lines);
    }

    /** Tokens that left one account for another. A transfer moves two sides,
     *  so a reader of this figure reads it per account, never across the book. */
    public function moved(string $handle): int
    {
        $sum = 0;
        foreach ($this->lines as $line) {
            if ($line->handle === $handle) {
                $sum += $line->signed();
            }
        }

        return $sum;
    }

    public function replay(string $handle): int
    {
        return $this->moved($handle);
    }
}
