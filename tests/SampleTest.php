<?php

declare(strict_types=1);

use Tokenup\Home\Project;
use Tokenup\Home\Sample;
use Tokenup\Tests\Assert;

return [
    ['the sample project sits at adjusted', static function (): void {
        Assert::same(Project::ADJUSTED, (new Sample())->project()->stage());
    }],
    ['the sample divides a million tokens', static function (): void {
        $sample = new Sample();
        Assert::same(1_000_000, $sample->tokenomics()->allocated());
        Assert::same(0, $sample->tokenomics()->unallocated());
    }],
    ['the sample carries five buckets', static function (): void {
        Assert::same(5, count((new Sample())->tokenomics()->allocations()));
    }],
    ['the sample holds no blocker and no warning', static function (): void {
        $review = (new Sample())->review();
        Assert::same(0, count($review->blockers()));
        Assert::same(0, count($review->warnings()));
    }],
    ['the stopped project waits at draft', static function (): void {
        [$stopped] = (new Sample())->stopped();
        Assert::same(Project::DRAFT, $stopped->stage());
    }],
];
