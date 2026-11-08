<?php

declare(strict_types=1);

use Tokenup\Home\Sample;
use Tokenup\Home\WhitePaper;
use Tokenup\Tests\Assert;

return [
    ['the sample drafts a paper', static function (): void {
        $sample = new Sample();
        Assert::true($sample->paper()->drafted());
        Assert::true($sample->paper()->words() > 0);
    }],
    ['the paper names the token and its network', static function (): void {
        $text = (new Sample())->paper()->draft();
        Assert::contains('Kestrel Works (KST)', $text);
        Assert::contains('Ethereum', $text);
        Assert::contains('erc-20', $text);
    }],
    ['the paper carries every bucket', static function (): void {
        $text = (new Sample())->paper()->draft();
        foreach (['community', 'liquidity', 'treasury', 'team', 'investors'] as $bucket) {
            Assert::contains($bucket, $text);
        }
    }],
    ['the paper carries every use', static function (): void {
        $text = (new Sample())->paper()->draft();
        foreach (['utility', 'demand', 'emission', 'treasury'] as $use) {
            Assert::contains('The ' . $use . ' case', $text);
        }
    }],
    ['a paper does not draft over a blocking finding', static function (): void {
        [$project, $tokenomics, $review] = (new Sample())->stopped();
        Assert::throws(static fn () => (new WhitePaper($project, $tokenomics, $review))->draft(), 'blocking findings');
    }],
    ['the paper runs past eighty words', static function (): void {
        Assert::true((new Sample())->paper()->words() > 80);
    }],
];
