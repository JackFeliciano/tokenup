<?php

declare(strict_types=1);

use Tokenup\Home\Draft;
use Tokenup\Home\Network;
use Tokenup\Home\Project;
use Tokenup\Home\Review;
use Tokenup\Home\Sample;
use Tokenup\Tests\Assert;

function tu_answered(): Draft
{
    $draft = new Draft('kestrel');
    foreach ([
        'name' => 'Kestrel Works', 'symbol' => 'KST', 'network' => 'ethereum', 'decimals' => 18,
        'initialSupply' => 1_000_000, 'maximumSupply' => 5_000_000, 'permit' => true,
        'mintable' => true, 'burnable' => true, 'pausable' => false,
        'unlimitedSupply' => false, 'initialLiquidity' => true,
    ] as $q => $v) {
        $draft->set($q, $v);
    }

    return $draft;
}

return [
    ['a project builds from an answered draft', static function (): void {
        $project = Project::fromDraft(tu_answered(), new Network('ethereum', 'Ethereum', 'erc-20'));
        Assert::same('KST', $project->symbol);
        Assert::same('5000000', $project->cap());
        Assert::same(Project::DRAFT, $project->stage());
    }],
    ['an unanswered draft does not build', static function (): void {
        Assert::throws(
            static fn () => Project::fromDraft(new Draft('x'), new Network('ethereum', 'Ethereum', 'erc-20')),
            'still asks for',
        );
    }],
    ['a symbol of three capitals holds', static function (): void {
        $draft = tu_answered();
        $draft->set('symbol', 'KST');
        Assert::same('KST', Project::fromDraft($draft, new Network('ethereum', 'Ethereum', 'erc-20'))->symbol);
    }],
    ['a lowercase symbol is refused', static function (): void {
        $draft = tu_answered();
        $draft->set('symbol', 'kst');
        Assert::throws(static fn () => Project::fromDraft($draft, new Network('ethereum', 'Ethereum', 'erc-20')), 'two to eight capitals');
    }],
    ['decimals off the network default are refused', static function (): void {
        $draft = tu_answered();
        $draft->set('decimals', 6);
        Assert::throws(static fn () => Project::fromDraft($draft, new Network('ethereum', 'Ethereum', 'erc-20')), 'decimals differ');
    }],
    ['a cap below the supply is refused', static function (): void {
        $draft = tu_answered();
        $draft->set('maximumSupply', 5);
        Assert::throws(static fn () => Project::fromDraft($draft, new Network('ethereum', 'Ethereum', 'erc-20')), 'cap sits below');
    }],
    ['the sample passes its review and reaches adjusted', static function (): void {
        $sample = new Sample();
        Assert::same(Project::ADJUSTED, $sample->project()->stage());
        Assert::same(0, count($sample->review()->blockers()));
    }],
    ['a project does not mint while a blocker stands', static function (): void {
        [$stopped, , $review] = (new Sample())->stopped();
        Assert::throws(static fn () => $stopped->mint($review), 'blocking findings');
        Assert::same(Project::DRAFT, $stopped->stage());
    }],
    ['a checked project mints when the review passes', static function (): void {
        $sample = new Sample();
        $sample->project()->mint($sample->review());
        Assert::same(Project::MINTED, $sample->project()->stage());
    }],
    ['a minted project does not take adjustments', static function (): void {
        $sample = new Sample();
        $sample->project()->mint($sample->review());
        Assert::throws(static fn () => $sample->project()->adjust(), 'only a checked project');
    }],
];
