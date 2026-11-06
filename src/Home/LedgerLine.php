<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * One line in the ledger. It records what moved, why, and what the account held
 * afterwards, so a later reader can replay the account from the lines alone.
 */
final class LedgerLine
{
    public function __construct(
        public readonly string $handle,
        public readonly string $direction,
        public readonly int $amount,
        public readonly string $why,
        public readonly int $balance,
    ) {
        if ($direction !== 'credit' && $direction !== 'debit') {
            throw new \RuntimeException('a ledger line moves one way or the other');
        }
    }

    public function signed(): int
    {
        return $this->direction === 'credit' ? $this->amount : -$this->amount;
    }
}
