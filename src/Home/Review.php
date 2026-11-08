<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The check that stands between the draft and the mint. The review reads the
 * project as a whole, the draft beside the tokenomics, and a blocking finding
 * closes the mint until the founder adjusts one of the answers.
 */
final class Review
{
    /** @var list<Finding> */
    private array $findings = [];

    public function __construct(
        public readonly Draft $draft,
        public readonly ?Project $project = null,
        public readonly ?Tokenomics $tokenomics = null,
    ) {
        $this->read();
    }

    private function add(string $code, string $level, string $saw, string $fix): void
    {
        $this->findings[] = new Finding($code, $level, $saw, $fix);
    }

    private function read(): void
    {
        foreach ($this->draft->missing() as $question) {
            $this->add('draft-' . $question, Finding::BLOCKER, 'The draft asks for ' . $question . ' and holds no answer.', 'Answer the question in the builder.');
        }
        if ($this->draft->missing() !== []) {
            return;
        }

        $supply = (int) $this->draft->answer('initialSupply');
        $max = $this->draft->answer('maximumSupply');
        $limit = is_int($max) ? $max : $supply;

        if ($supply <= 0) {
            $this->add('supply-zero', Finding::BLOCKER, 'The initial supply is ' . $supply . '.', 'Set a supply above zero.');
        }
        if (is_int($max) && $max < $supply) {
            $this->add('cap-below-supply', Finding::BLOCKER, 'The cap of ' . $max . ' sits below the initial supply of ' . $supply . '.', 'Raise the cap or lower the supply.');
        }
        if ($this->draft->answer('unlimitedSupply') === true && $this->draft->answer('mintable') !== true) {
            $this->add('unlimited-without-mint', Finding::BLOCKER, 'The draft asks for unlimited supply with minting off.', 'Turn minting on or set a cap.');
        }
        if (is_int($max) && !in_array($max, [0, $supply], true) && $this->draft->answer('mintable') !== true) {
            $this->add('cap-without-mint', Finding::WARNING, 'A cap of ' . $max . ' exists and nothing can mint toward it.', 'Turn minting on or keep the supply fixed.');
        }

        $tokenomics = $this->tokenomics;
        if ($tokenomics === null) {
            $this->add('no-tokenomics', Finding::BLOCKER, 'The project carries no tokenomics.', 'Answer the tokenomics questions.');
        } else {
            $this->readTokenomics($tokenomics, $supply, $limit);
        }

        if ($this->draft->answer('initialLiquidity') === true && $tokenomics !== null) {
            $pool = null;
            foreach ($tokenomics->allocations() as $allocation) {
                if ($allocation->bucket === 'liquidity') {
                    $pool = $allocation;
                }
            }
            if ($pool === null) {
                $this->add('pool-without-slice', Finding::BLOCKER, 'The draft opens a liquidity pool and no allocation carries the bucket liquidity.', 'Add a liquidity allocation or leave the pool closed.');
            } elseif ($tokenomics->share($pool) < 500) {
                $this->add('pool-slice-thin', Finding::WARNING, 'Liquidity holds ' . $tokenomics->share($pool) . ' basis points of the supply.', 'Consider a larger liquidity slice before the token trades.');
            }
        }
    }

    private function readTokenomics(Tokenomics $tokenomics, int $supply, int $limit): void
    {
        if ($tokenomics->totalSupply !== $supply) {
            $this->add('supply-mismatch', Finding::BLOCKER, 'The draft mints ' . $supply . ' and the tokenomics divides ' . $tokenomics->totalSupply . '.', 'Make the two figures agree.');
        }
        if ($tokenomics->unallocated() > 0) {
            $this->add('unallocated', Finding::BLOCKER, 'The allocations leave ' . $tokenomics->unallocated() . ' tokens in no bucket.', 'Place the rest of the supply.');
        }
        if (!$tokenomics->complete()) {
            $this->add('uses-missing', Finding::WARNING, 'Some uses of the token hold no answer yet.', 'Answer what the token does for its holders.');
        }
        foreach ($tokenomics->allocations() as $allocation) {
            if ($allocation->lockMonths > 0 && $tokenomics->cliffMonths === 0) {
                $this->add('lock-without-cliff', Finding::WARNING, 'Bucket ' . $allocation->bucket . ' locks for ' . $allocation->lockMonths . ' months with no cliff.', 'State the cliff the holder waits out.');
            }
            if ($allocation->bucket === 'team' && $tokenomics->share($allocation) > 2000) {
                $this->add('team-heavy', Finding::WARNING, 'Team holds ' . $tokenomics->share($allocation) . ' basis points.', 'Weigh a smaller team slice before the sale.');
            }
            if ($allocation->bucket === 'investors' && $allocation->lockMonths === 0) {
                $this->add('investors-unlocked', Finding::BLOCKER, 'The investors bucket unlocks on day one.', 'Lock the investor slice.');
            }
            if ($allocation->tokens > $limit) {
                $this->add('slice-above-cap', Finding::BLOCKER, 'Bucket ' . $allocation->bucket . ' carries more tokens than the cap allows.', 'Bring the slice under the cap.');
            }
        }
    }

    /** @return list<Finding> */
    public function findings(): array
    {
        return $this->findings;
    }

    /** @return list<Finding> */
    public function blockers(): array
    {
        return array_values(array_filter($this->findings, static fn (Finding $f): bool => $f->blocks()));
    }

    /** @return list<Finding> */
    public function warnings(): array
    {
        return array_values(array_filter($this->findings, static fn (Finding $f): bool => !$f->blocks()));
    }

    public function passes(): bool
    {
        return $this->blockers() === [];
    }
}
