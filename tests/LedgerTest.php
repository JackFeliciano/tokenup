<?php

declare(strict_types=1);

use Tokenup\Home\Account;
use Tokenup\Home\Ledger;
use Tokenup\Home\Token;
use Tokenup\Tests\Assert;

return [
    ['a replay of the lines reaches the same balance', static function (): void {
        $ledger = new Ledger();
        $a = new Account('tu-1', 'Ana Reis');
        $b = new Account('tu-2', 'Bright Field');
        $ledger->record($a->credit(100, 'opening balance'));
        $ledger->record($b->credit(20, 'opening balance'));
        $ledger->record(...$a->transfer(30, $b, 'hours booked'));
        Assert::same(70, $ledger->replay('tu-1'));
        Assert::same(50, $ledger->replay('tu-2'));
        Assert::same(70, $a->tokens());
        Assert::same(50, $b->tokens());
    }],
    ['the moved figure adds the signed lines of one account', static function (): void {
        $ledger = new Ledger();
        $a = new Account('tu-1', 'Ana Reis');
        $b = new Account('tu-2', 'Bright Field');
        $ledger->record($a->credit(130, 'opening balance'));
        $ledger->record(...$a->transfer(30, $b, 'hours booked'));
        $ledger->record($a->debit(24, 'network fee'));
        Assert::same(4, $ledger->count());
        Assert::same(76, $ledger->moved('tu-1'));
        Assert::same(30, $ledger->moved('tu-2'));
    }],
    ['a ledger line moves one way or the other', static function (): void {
        $ledger = new Ledger();
        Assert::throws(static fn () => $ledger->record(new \Tokenup\Home\LedgerLine('tu-1', 'sideways', 1, 'odd', 1)), 'one way or the other');
    }],
    ['a token carries a supply and burns down', static function (): void {
        $token = new Token('TUP', 'hour', 100);
        $token->burn(40);
        Assert::same(60, $token->supply());
        $token->mint(10);
        Assert::same(70, $token->supply());
    }],
    ['a token cannot burn or mint nothing', static function (): void {
        $token = new Token('TUP', 'hour', 100);
        Assert::throws(static fn () => $token->burn(0), 'one token or more');
        Assert::throws(static fn () => $token->mint(0), 'one token or more');
        Assert::throws(static fn () => $token->burn(101), 'fewer tokens');
        Assert::throws(static fn () => new Token('TUP', 'hour', 0), 'supply of one or more');
    }],
];
