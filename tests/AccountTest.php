<?php

declare(strict_types=1);

use Tokenup\Home\Account;
use Tokenup\Tests\Assert;

return [
    ['an account starts at zero or above', static function (): void {
        Assert::same(0, (new Account('tu-1', 'Ana Reis'))->tokens());
        Assert::throws(static fn () => new Account('tu-1', 'Ana Reis', -1), 'zero or above');
    }],
    ['a credit adds and a debit takes away', static function (): void {
        $account = new Account('tu-1', 'Ana Reis', 10);
        $account->credit(5, 'work delivered');
        Assert::same(15, $account->tokens());
        $account->debit(4, 'network fee');
        Assert::same(11, $account->tokens());
    }],
    ['a credit carries one token or more', static function (): void {
        $account = new Account('tu-1', 'Ana Reis', 10);
        Assert::throws(static fn () => $account->credit(0, 'nothing'), 'one token or more');
        Assert::throws(static fn () => $account->debit(-2, 'odd'), 'one token or more');
    }],
    ['an account cannot spend more than it holds', static function (): void {
        $account = new Account('tu-1', 'Ana Reis', 10);
        Assert::throws(static fn () => $account->debit(11, 'network fee'), 'fewer tokens');
        Assert::same(10, $account->tokens());
    }],
    ['a transfer moves tokens and leaves the total alone', static function (): void {
        $from = new Account('tu-1', 'Ana Reis', 40);
        $to = new Account('tu-2', 'Bright Field', 5);
        $lines = $from->transfer(15, $to, 'hours booked');
        Assert::same(25, $from->tokens());
        Assert::same(20, $to->tokens());
        Assert::same(-15, $lines[0]->signed());
        Assert::same(15, $lines[1]->signed());
    }],
    ['an account cannot send to itself', static function (): void {
        $account = new Account('tu-1', 'Ana Reis', 10);
        Assert::throws(static fn () => $account->transfer(1, $account, 'loop'), 'cannot send to itself');
    }],
    ['a ledger line records the balance after it', static function (): void {
        $account = new Account('tu-1', 'Ana Reis', 10);
        $line = $account->credit(5, 'work delivered');
        Assert::same(15, $line->balance);
        Assert::same('credit', $line->direction);
        Assert::same('work delivered', $line->why);
    }],
];
