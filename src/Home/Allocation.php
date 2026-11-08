<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * One slice of the supply and the day its holder first gets to move it. A slice
 * belongs to exactly one bucket, so two slices never share a name.
 */
final class Allocation
{
    public function __construct(
        public readonly string $bucket,
        public readonly int $tokens,
        public readonly string $holder = '',
        public readonly int $lockMonths = 0,
    ) {
        if (trim($bucket) === '') {
            throw new \InvalidArgumentException('an allocation carries a bucket');
        }
        if ($tokens <= 0) {
            throw new \InvalidArgumentException('an allocation carries tokens');
        }
        if ($lockMonths < 0) {
            throw new \InvalidArgumentException('an allocation carries no negative lock');
        }
    }
}
