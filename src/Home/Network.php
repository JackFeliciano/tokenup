<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The networks a project can aim at. A network carries the standard its contract
 * follows and the decimals its tokens use by default.
 */
final class Network
{
    public const STANDARDS = ['erc-20', 'spl'];

    public function __construct(
        public readonly string $handle,
        public readonly string $label,
        public readonly string $standard,
        public readonly int $decimals = 18,
    ) {
        if (!in_array($standard, self::STANDARDS, true)) {
            throw new \InvalidArgumentException('unknown standard: ' . $standard);
        }
        if ($decimals < 0 || $decimals > 18) {
            throw new \InvalidArgumentException('decimals outside 0 to 18');
        }
    }
}
