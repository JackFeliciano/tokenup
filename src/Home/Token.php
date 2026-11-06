<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * A token is a claim on one unit of work inside the network. It carries a supply
 * and a unit name, so a reader can tell a token of one network from another.
 */
final class Token
{
    public function __construct(
        public readonly string $symbol,
        public readonly string $unit,
        private int $supply,
    ) {
        if ($supply < 1) {
            throw new \RuntimeException('a token carries a supply of one or more');
        }
    }

    public function supply(): int
    {
        return $this->supply;
    }

    public function burn(int $amount): void
    {
        if ($amount < 1) {
            throw new \RuntimeException('a burn removes one token or more');
        }
        if ($amount > $this->supply) {
            throw new \RuntimeException('the supply holds fewer tokens than that');
        }
        $this->supply -= $amount;
    }

    public function mint(int $amount): void
    {
        if ($amount < 1) {
            throw new \RuntimeException('a mint adds one token or more');
        }
        $this->supply += $amount;
    }
}
