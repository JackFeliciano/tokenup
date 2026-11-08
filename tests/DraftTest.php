<?php

declare(strict_types=1);

use Tokenup\Home\Draft;
use Tokenup\Tests\Assert;

return [
    ['an empty draft asks for every question', static function (): void {
        Assert::same(12, count((new Draft('a'))->missing()));
    }],
    ['an answer leaves the missing list', static function (): void {
        $draft = new Draft('a');
        $draft->set('name', 'Kestrel Works');
        Assert::false(in_array('name', $draft->missing(), true));
        Assert::same('Kestrel Works', $draft->answer('name'));
    }],
    ['an unknown question is refused', static function (): void {
        Assert::throws(static fn () => (new Draft('a'))->set('website', 'x'), 'unknown question');
    }],
    ['a draft without a handle is refused', static function (): void {
        Assert::throws(static fn () => new Draft(' '), 'a draft carries a handle');
    }],
];
