<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The contract options the builder asks about. Each switch answers a question a
 * founder can answer before a line of Solidity exists.
 */
final class Features
{
    public function __construct(
        public readonly bool $permit = false,
        public readonly bool $mintable = false,
        public readonly bool $burnable = false,
        public readonly bool $pausable = false,
        public readonly bool $unlimitedSupply = false,
        public readonly bool $initialLiquidity = false,
    ) {
        if ($unlimitedSupply && !$mintable) {
            throw new \InvalidArgumentException('unlimited supply needs minting');
        }
    }

    public function label(string $name): string
    {
        return match ($name) {
            'permit' => $this->permit ? 'permits' : 'no permit',
            'mintable' => $this->mintable ? 'mints' : 'fixed supply',
            'burnable' => $this->burnable ? 'burns' : 'no burn',
            'pausable' => $this->pausable ? 'pauses' : 'never pauses',
            'unlimitedSupply' => $this->unlimitedSupply ? 'no cap' : 'capped',
            'initialLiquidity' => $this->initialLiquidity ? 'opens a pool' : 'no pool',
            default => throw new \InvalidArgumentException('unknown feature: ' . $name),
        };
    }
}
