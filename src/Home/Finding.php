<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * One thing the review found. A finding names the code it carries, what it saw,
 * why it matters and what the founder can change about it.
 */
final class Finding
{
    public const BLOCKER = 'blocker';
    public const WARNING = 'warning';

    public function __construct(
        public readonly string $code,
        public readonly string $level,
        public readonly string $saw,
        public readonly string $fix,
    ) {
        if (!in_array($level, [self::BLOCKER, self::WARNING], true)) {
            throw new \InvalidArgumentException('unknown level: ' . $level);
        }
    }

    public function blocks(): bool
    {
        return $this->level === self::BLOCKER;
    }
}
