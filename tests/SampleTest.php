<?php

declare(strict_types=1);

use Tokenup\Home\Sample;
use Tokenup\Tests\Assert;

return [
    ['the sample holds three accounts', static function (): void {
        Assert::same(3, count((new Sample())->accounts()));
    }],
    ['the sample ledger holds four lines', static function (): void {
        Assert::same(4, (new Sample())->ledger()->count());
    }],
    ['the accounts and the ledger agree', static function (): void {
        Assert::same(true, (new Sample())->agrees());
    }],
    ['the held tokens add the three balances', static function (): void {
        $sample = new Sample();
        $sum = 0;
        foreach ($sample->accounts() as $account) {
            $sum += $account->tokens();
        }
        Assert::same($sum, $sample->held());
        Assert::same(216, $sample->held());
    }],
];
