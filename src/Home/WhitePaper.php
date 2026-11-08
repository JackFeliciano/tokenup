<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The paper the system writes once the tokenomics carry every answer. The paper
 * cannot be drafted from a draft that still holds a blocking finding, because a
 * paper describes a token that can actually be minted.
 */
final class WhitePaper
{
    private bool $drafted = false;

    public function __construct(
        public readonly Project $project,
        public readonly Tokenomics $tokenomics,
        public readonly Review $review,
    ) {
    }

    public function draft(): string
    {
        if (!$this->review->passes()) {
            throw new \RuntimeException('the paper waits on ' . count($this->review->blockers()) . ' blocking findings');
        }
        $this->drafted = true;

        return $this->text();
    }

    public function drafted(): bool
    {
        return $this->drafted;
    }

    public function words(): int
    {
        return str_word_count($this->text());
    }

    private function text(): string
    {
        $p = $this->project;
        $t = $this->tokenomics;
        $f = $p->features;
        $out = [];

        $out[] = $p->name . ' (' . $p->symbol . ')';
        $out[] = 'The token runs on ' . $p->network->label . ' and follows the ' . $p->network->standard
            . ' standard with ' . $p->decimals . ' decimals. It mints ' . number_format($p->initialSupply)
            . ' tokens at launch and its supply is ' . $p->cap() . '.';

        $out[] = 'The contract ' . $f->label('permit') . ', ' . $f->label('mintable') . ', ' . $f->label('burnable')
            . ' and ' . $f->label('pausable') . '. ' . ($f->initialLiquidity ? 'A liquidity pool opens at launch.' : 'No pool opens at launch.');

        $out[] = 'Allocation';
        foreach ($t->allocations() as $allocation) {
            $share = number_format($t->share($allocation) / 100, 2);
            $out[] = 'The bucket ' . $allocation->bucket . ' holds ' . $share . ' percent, which is '
                . number_format($allocation->tokens) . ' ' . $p->symbol
                . ($allocation->holder !== '' ? ', held by ' . $allocation->holder : '')
                . ($allocation->lockMonths > 0 ? ', locked for ' . $allocation->lockMonths . ' months after a cliff of ' . $t->cliffMonths . ' months' : '')
                . '.';
        }

        $out[] = 'What the token does';
        foreach (Tokenomics::USE as $key) {
            $out[] = 'The ' . $key . ' case: ' . (string) $t->useText($key);
        }

        return implode("\n\n", $out);
    }
}
