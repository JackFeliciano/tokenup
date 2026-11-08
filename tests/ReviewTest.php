<?php

declare(strict_types=1);

use Tokenup\Home\Allocation;
use Tokenup\Home\Draft;
use Tokenup\Home\Network;
use Tokenup\Home\Project;
use Tokenup\Home\Review;
use Tokenup\Home\Sample;
use Tokenup\Home\Tokenomics;
use Tokenup\Tests\Assert;

function tu_project(): Project
{
    return Project::fromDraft(tu_answered(), new Network('ethereum', 'Ethereum', 'erc-20'));
}

function tu_codes(array $findings): array
{
    $codes = [];
    foreach ($findings as $finding) {
        $codes[] = $finding->code;
    }

    return $codes;
}

return [
    ['a draft with no answers blocks on every question', static function (): void {
        $review = new Review(new Draft('x'));
        Assert::false($review->passes());
        Assert::same(12, count($review->blockers()));
    }],
    ['the sample passes and carries no blocker', static function (): void {
        $sample = new Sample();
        Assert::true($sample->review()->passes());
        Assert::same([], $sample->review()->blockers());
    }],
    ['the stopped project blocks', static function (): void {
        [, , $review] = (new Sample())->stopped();
        Assert::false($review->passes());
        Assert::same('supply-mismatch', tu_codes($review->findings())[0]);
    }],
    ['the stopped project leaves the pool unfilled', static function (): void {
        [, , $review] = (new Sample())->stopped();
        Assert::true(in_array('pool-without-slice', tu_codes($review->findings()), true));
    }],
    ['a team slice over a fifth of the supply warns', static function (): void {
        $tokenomics = new Tokenomics(1_000_000);
        $tokenomics->allocate(new Allocation('team', 500_000));
        $tokenomics->allocate(new Allocation('community', 500_000));
        $review = new Review(tu_answered(), tu_project(), $tokenomics);
        Assert::true(in_array('team-heavy', tu_codes($review->warnings()), true));
    }],
    ['an unlocked investor slice blocks the mint', static function (): void {
        $draft = tu_answered();
        $tokenomics = new Tokenomics(1_000_000);
        $tokenomics->allocate(new Allocation('community', 500_000));
        $tokenomics->allocate(new Allocation('investors', 500_000));
        $review = new Review($draft, tu_project(), $tokenomics);
        Assert::true(in_array('investors-unlocked', tu_codes($review->blockers()), true));
    }],
    ['a project with no tokenomics blocks', static function (): void {
        $review = new Review(tu_answered(), tu_project(), null);
        Assert::same(['no-tokenomics'], tu_codes($review->blockers()));
    }],
    ['a supply that differs from the tokenomics blocks', static function (): void {
        $tokenomics = new Tokenomics(900_000);
        $tokenomics->allocate(new Allocation('community', 900_000));
        $review = new Review(tu_answered(), tu_project(), $tokenomics);
        Assert::true(in_array('supply-mismatch', tu_codes($review->blockers()), true));
    }],
    ['unallocated tokens block', static function (): void {
        $tokenomics = new Tokenomics(1_000_000);
        $tokenomics->allocate(new Allocation('community', 600_000));
        $review = new Review(tu_answered(), tu_project(), $tokenomics);
        Assert::true(in_array('unallocated', tu_codes($review->blockers()), true));
    }],
];
