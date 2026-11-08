<?php

declare(strict_types=1);

use Tokenup\Home\Allocation;
use Tokenup\Home\Tokenomics;
use Tokenup\Tests\Assert;

return [
    ['allocations add to the total', static function (): void {
        $t = new Tokenomics(1000);
        $t->allocate(new Allocation('team', 400));
        $t->allocate(new Allocation('community', 600));
        foreach (['utility', 'demand', 'emission', 'treasury'] as $key) {
            $t->use($key, 'A reason for ' . $key . '.');
        }
        Assert::same(1000, $t->allocated());
        Assert::same(0, $t->unallocated());
        Assert::true($t->complete());
    }],
    ['an empty tokenomics is incomplete', static function (): void {
        Assert::false((new Tokenomics(1000))->complete());
        Assert::same(1000, (new Tokenomics(1000))->unallocated());
    }],
    ['an allocation past the total is refused', static function (): void {
        $t = new Tokenomics(1000);
        $t->allocate(new Allocation('team', 900));
        Assert::throws(static fn () => $t->allocate(new Allocation('community', 200)), 'passes the total supply');
    }],
    ['two buckets do not share a name', static function (): void {
        $t = new Tokenomics(1000);
        $t->allocate(new Allocation('team', 100));
        Assert::throws(static fn () => $t->allocate(new Allocation('team', 100)), 'duplicate bucket');
    }],
    ['an allocation of nothing is refused', static function (): void {
        Assert::throws(static fn () => new Allocation('team', 0), 'carries tokens');
    }],
    ['a share counts basis points', static function (): void {
        $t = new Tokenomics(1000);
        $slice = new Allocation('team', 250);
        $t->allocate($slice);
        Assert::same(2500, $t->share($slice));
    }],
    ['a cliff longer than the hold is refused', static function (): void {
        Assert::throws(static fn () => new Tokenomics(1000, 6, 12), 'cliff runs longer');
    }],
    ['an empty use answer is refused', static function (): void {
        Assert::throws(static fn () => (new Tokenomics(1000))->use('utility', ' '), 'empty use');
    }],
    ['an unknown use is refused', static function (): void {
        Assert::throws(static fn () => (new Tokenomics(1000))->use('vibes', 'x'), 'unknown use');
    }],
];
