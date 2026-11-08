<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The token as the founder settled it. A project exists only once the answers
 * hold, and it moves through draft, checked, adjusted and minted.
 */
final class Project
{
    public const DRAFT = 'draft';
    public const CHECKED = 'checked';
    public const ADJUSTED = 'adjusted';
    public const MINTED = 'minted';
    public const STAGES = [self::DRAFT, self::CHECKED, self::ADJUSTED, self::MINTED];

    private string $stage = self::DRAFT;

    public function __construct(
        public readonly string $handle,
        public readonly string $name,
        public readonly string $symbol,
        public readonly Network $network,
        public readonly int $decimals,
        public readonly int $initialSupply,
        public readonly ?int $maximumSupply,
        public readonly Features $features,
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('a project carries a name');
        }
        if (!preg_match('/^[A-Z0-9]{2,8}$/', $symbol)) {
            throw new \InvalidArgumentException('a symbol holds two to eight capitals and digits');
        }
        if ($decimals !== $network->decimals) {
            throw new \InvalidArgumentException('the decimals differ from the network default of ' . $network->decimals);
        }
        if ($initialSupply <= 0) {
            throw new \InvalidArgumentException('a project carries an initial supply');
        }
        if ($maximumSupply !== null && $maximumSupply < $initialSupply) {
            throw new \InvalidArgumentException('the cap sits below the initial supply');
        }
        if ($maximumSupply !== null && $features->unlimitedSupply) {
            throw new \InvalidArgumentException('an uncapped token carries no maximum supply');
        }
    }

    public static function fromDraft(Draft $draft, Network $network): self
    {
        if ($draft->missing() !== []) {
            throw new \InvalidArgumentException('the draft still asks for ' . implode(', ', $draft->missing()));
        }
        $max = $draft->answer('maximumSupply');

        return new self(
            $draft->handle,
            (string) $draft->answer('name'),
            (string) $draft->answer('symbol'),
            $network,
            (int) $draft->answer('decimals'),
            (int) $draft->answer('initialSupply'),
            is_int($max) && $max > 0 ? $max : null,
            new Features(
                (bool) $draft->answer('permit'),
                (bool) $draft->answer('mintable'),
                (bool) $draft->answer('burnable'),
                (bool) $draft->answer('pausable'),
                (bool) $draft->answer('unlimitedSupply'),
                (bool) $draft->answer('initialLiquidity'),
            ),
        );
    }

    public function stage(): string
    {
        return $this->stage;
    }

    public function check(Review $review): void
    {
        if ($this->stage !== self::DRAFT) {
            throw new \RuntimeException('only a draft goes to review');
        }
        if (!$review->passes()) {
            throw new \RuntimeException('review holds ' . count($review->blockers()) . ' blocking findings');
        }
        $this->stage = self::CHECKED;
    }

    public function adjust(): void
    {
        if ($this->stage !== self::CHECKED) {
            throw new \RuntimeException('only a checked project takes adjustments');
        }
        $this->stage = self::ADJUSTED;
    }

    public function mint(Review $review): void
    {
        if (!$review->passes()) {
            throw new \RuntimeException('the mint waits on ' . count($review->blockers()) . ' blocking findings');
        }
        if (!in_array($this->stage, [self::CHECKED, self::ADJUSTED], true)) {
            throw new \RuntimeException('a ' . $this->stage . ' project does not mint');
        }
        $this->stage = self::MINTED;
    }

    public function cap(): string
    {
        return $this->maximumSupply === null ? 'uncapped' : (string) $this->maximumSupply;
    }
}
